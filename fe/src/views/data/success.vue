<template>
  <div class="container bigtable">
    <Breadcrumb :items="['menu.data', 'menu.data.back']" />

    <a-card class="general-card" style="padding-top: 30px">
      <a-row>
        <a-col :flex="1">
          <a-form
            :model="formModel"
            :label-col-props="{ span: 7 }"
            :wrapper-col-props="{ span: 17 }"
            label-align="left"
            :auto-label-width="true"
          >
            <a-row :gutter="16">
              <a-col :span="6">
                <a-form-item field="name" label="客户姓名">
                  <a-input
                    v-model="formModel.name"
                    placeholder="请输入"
                  />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="mobile" label="客户手机号">
                  <a-input
                    v-model="formModel.mobile"
                    placeholder="请输入"
                  />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="follow_user_id" label="跟进顾问">
                  <a-select
                    v-model="formModel.follow_user_id"
                    :options="userList"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="team_id" label="团队">
                  <a-tree-select
                    v-model="formModel.team_id"
                    :data="teamList"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
            </a-row>
            <a-row :gutter="16">
              <a-col :span="6">
                <a-form-item field="name" label="申请时间">
                  <a-range-picker
                    v-model="formModel.applyTime"
                    style="width: 100%"
                    show-time
                  />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="name" label="回款时间">
                  <a-range-picker
                    v-model="formModel.backTime"
                    style="width: 100%"
                    show-time
                  />
                </a-form-item>
              </a-col>
            </a-row>
          </a-form>
        </a-col>
      </a-row>
      <a-divider style="margin-top: 0" />
      <a-row style="margin-bottom: 16px">
        <a-col :span="16">
          <a-space>
            <a-button type="primary" @click="search">
              <template #icon> <icon-search /> </template> 查询
            </a-button>
            <a-button type="primary" @click="download">
              <template #icon> <icon-download /> </template> 导出
            </a-button>
          </a-space>
        </a-col>
      </a-row>
      <a-table
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        :data="renderData"
        :bordered="false"
        label-align="center"
        @page-change="onPageChange"
      >
        <template #columns>
          <a-table-column title="id" data-index="id" />
          <a-table-column title="客户姓名" data-index="name" />
          <a-table-column title="放款时间" data-index="date" :width="170"/>
          <a-table-column title="放款金额" data-index="amount" />
          <a-table-column title="点位" data-index="fee" />
          <a-table-column title="实际创收" data-index="real_amount" />
          <a-table-column title="成本费用" data-index="cost" />
          <a-table-column title="跟进顾问" data-index="follow_user" />
          <a-table-column title="团队" data-index="team" />
          <a-table-column title="数据来源" data-index="user_from" />
          <a-table-column title="渠道" data-index="source" />
          <a-table-column title="备注" >
            <template #cell="{ record }">
                <span :title="record.remark">{{ record.remark_title}}</span>
            </template>
          </a-table-column>
          <a-table-column title="操作" data-index="operations">
            <template #cell="{ record }">
              <a-button
                v-if="userStore.$state.buttonPermission.includes('EditBack')"
                type="text"
                size="small"
                @click="edit(record)"
              >
                <icon-edit /> 编辑
              </a-button>
              <a-popconfirm
                v-if="userStore.$state.buttonPermission.includes('DelBack')"
                content="确认删除回款？"
                ok-text="确认"
                cancel-text="取消"
                @ok="del(record.id)"
              >
                <a-button type="text" size="small">
                  <icon-delete /> 删除</a-button
                >
              </a-popconfirm>
            </template>
          </a-table-column>
        </template>
      </a-table>
      <a-modal v-model:visible="backModal" :width="700" :footer="false">
        <template #title> 添加回款 </template>
        <a-form :model="backform">
          <a-form-item field="date" label="放款时间">
            <a-date-picker
              v-model="backform.date"
              show-time
              format="YYYY-MM-DD HH:mm:ss"
            />
          </a-form-item>
          <a-form-item field="amount" label="放款金额">
            <a-input-number
              v-model="backform.amount"
              placeholder="请输入"
              hide-button
              @change="computeRealMoney"
            >
              <template #append>元</template>
            </a-input-number>
          </a-form-item>
          <a-form-item field="agency_fee" label="点位(%)">
            <a-input-number
              v-model="backform.agency_fee"
              placeholder="请输入"
              hide-button
              @change="computeRealMoney"
            >
              <template #append>%</template>
            </a-input-number>
          </a-form-item>
          <a-form-item field="agency_fee" label="实际创收">
            <a-input-number
              v-model="backform.realmoney"
              placeholder=""
              hide-button
            >
              <template #append>元</template>
            </a-input-number>
          </a-form-item>
          <a-form-item field="cost" label="成本费用">
            <a-input-number
              v-model="backform.cost"
              placeholder=""
              hide-button
            >
              <template #append>元</template>
            </a-input-number>
          </a-form-item>
          <a-form-item field="hetong" label="合同编号">
            <a-input
              v-model="backform.hetong"
              placeholder=""
              hide-button
            >
            </a-input>
          </a-form-item>
          <a-form-item label="备注">
            <a-textarea v-model="backform.remark" placeholder="请输入" />
          </a-form-item>
          <a-form-item>
            <a-button type="primary" @click="submitBack">保存</a-button>
          </a-form-item>
        </a-form>
      </a-modal>
    </a-card>
  </div>
</template>

<script lang="ts" setup>
  import { ref, reactive } from 'vue';
  import useLoading from '@/hooks/loading';
  import { Pagination } from '@/types/global';
  import {
    BackList,
    BackInfo,
    queryBackList,
    BackListParams,
    editBacks,
    delBack,
  } from '@/api/data';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import { Message, Modal } from '@arco-design/web-vue';
  import { useAppStore } from '@/store';

  const userStore = useAppStore();

  const { loading, setLoading } = useLoading(true);
  const renderData = ref<BackInfo[]>([]);
  const formModel = ref({
    name: '',
    mobile: '',
    follow_user_id: '',
    team_id: '',
    applyTime: [],
    backTime: [],
  });
  const basePagination: Pagination = { current: 1, pageSize: 20 };
  const pagination = reactive({ ...basePagination });
  const teamList = ref<SelectOptionData[]>([]);
  const userList = ref<SelectOptionData[]>([]);
  const backModal = ref(false);
  const backform = ref({
    id: 0,
    amount: '' as unknown as number,
    date: '',
    agency_fee: '' as unknown as number,
    remark: '',
    realmoney: '' as unknown as number,
    cost: '' as unknown as number,
    hetong: '' ,
  });

  const computeRealMoney = () => {
    backform.value.realmoney =
      (backform.value.amount * backform.value.agency_fee) / 100;
  };
  const fetchData = async (
    params: BackListParams = { current: 1, pageSize: 20 }
  ) => {
    setLoading(true);
    try {
      const { data } = await queryBackList(params);
      renderData.value = data.list;
      pagination.current = params.current;
      pagination.total = data.total;
      teamList.value = data.allTeamListSelect;
      userList.value = data.followUserList;
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };

  const search = () => {
    fetchData({
      ...basePagination,
      ...formModel.value,
      'applyTime[]': formModel.value.applyTime,
      'backTime[]': formModel.value.backTime,
    } as unknown as BackListParams);
  };
  const onPageChange = (current: number) => {
     fetchData({ ...basePagination, current });
  };

  const edit = (record: any) => {
    console.log(record);
    backform.value.id = record.id;
    backform.value.amount = record.amount;
    backform.value.date = record.date;
    backform.value.agency_fee = parseFloat(record.fee);
    backform.value.remark = record.remark;
    backform.value.realmoney = record.real_amount;
    backform.value.cost = record.cost;
    backform.value.hetong= record.hetong;
    backModal.value = true;
  };

  const del = async (id: any) => {
    await delBack(id);
    Message.success({
      content: '操作成功',
      duration: 5 * 1000,
    });
    search();
  };
  fetchData();

  const submitBack = async () => {
    try {
      const { data } = await editBacks(backform.value);
      backModal.value = false;
      Message.success({
        content: '操作成功',
        duration: 5 * 1000,
      });
      search();
    } catch (err) {
      console.log(err);
    }
  };

  const download = () => {
    const u = new URLSearchParams({
      current: "1",
      pageSize: "10000",
      ...formModel.value,
    } as unknown as string[][]).toString();
    (window as Window).location = `/api/data/backlist?export=1&${u}`;
  };
</script>

<style scoped lang="less">

  .container {
    padding: 0 20px 20px 20px;
  }
</style>


<style>
  .bigtable .arco-table .arco-table-cell {
    padding: 5px 10px;
  }

  .bigtable .arco-link,
  .container.bigtable .arco-table .arco-table-th,
  .bigtable .arco-table .arco-table-td {
    font-size: 12px;
  }
  .bigtable .arco-link{
    color:rgb(var(--gray-10))
  }
</style>