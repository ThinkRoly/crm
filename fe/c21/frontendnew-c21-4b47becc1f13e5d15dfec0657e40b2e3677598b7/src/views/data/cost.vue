<template>
  <div class="container bigtable">
    <Breadcrumb :items="['menu.data', 'menu.data.cost']" />

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
              <a-col :span="4">
                <a-form-item field="name" label="跟进顾问">
                  <a-select
                    v-model="formModel.user_id"
                    :options="userList"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="4">
                <a-form-item field="name" label="团队">
                  <a-tree-select
                    v-model="formModel.team_id"
                    :data="teamList"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="16">
                <a-form-item field="name" label="时间">
                  <a-radio-group v-model="formModel.timeType" type="button">
                    <a-radio value="1">今日</a-radio>
                    <a-radio value="2">昨日</a-radio>
                    <a-radio value="3">近三天</a-radio>
                    <a-radio value="4">本周</a-radio>
                    <a-radio value="5">本月</a-radio>
                    <a-radio value="6">自定义</a-radio>
                  </a-radio-group>
                  <a-range-picker
                    v-if="formModel.timeType == '6'"
                    v-model="formModel.time"
                    style="width: 240px"
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
          <a-table-column title="用户" data-index="name" />
          <a-table-column title="团队" data-index="team" />
          <a-table-column title="角色" data-index="roles" />
          <a-table-column title="名下数据" data-index="all" />
          <a-table-column title="新数据" data-index="new" />
          <a-table-column title="再分配" data-index="old" />
          <a-table-column title="数据费用" data-index="cost" />
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script lang="ts" setup>
  import { ref, reactive } from 'vue';
  import useLoading from '@/hooks/loading';
  import { Pagination } from '@/types/global';
  import { queryCostList, ChannelListParams } from '@/api/data';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import { useAppStore } from '@/store';

  const { loading, setLoading } = useLoading(true);
  const userStore = useAppStore();
  const renderData = ref([]);
  const formModel = ref({
    user_id: '',
    team_id: '',
    timeType: '6',
    time: ['2021-01-01', '2024-01-01'],
  });
  const timeType = ref({
    timeType: '3',
    stime: '',
    etime: '',
    time: ['2021-01-01', '2024-01-01'],
  });
  const basePagination: Pagination = { current: 1, pageSize: 1000 };
  const pagination = reactive({ ...basePagination });
  const teamList = ref<SelectOptionData[]>([]);
  const userList = ref<SelectOptionData[]>([]);

  const fetchData = async (
    params: ChannelListParams = {
      current: 1,
      pageSize: 20,
      timeType: '6',
      type: 'user',
    }
  ) => {
    setLoading(true);
    try {
      params.timeType = formModel.value.timeType;
      const { data } = await queryCostList(params);
      timeType.value.timeType = formModel.value.timeType;
      timeType.value.time = formModel.value.time;
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
      'times[]': formModel.value.time,
    } as unknown as ChannelListParams);
  };
  const onPageChange = (current: number) => {
    fetchData({
      ...basePagination,
      current,
      timeType: formModel.value.timeType,
      type: 'user',
    });
  };

  search();
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

  .bigtable .arco-link {
    color: rgb(var(--gray-10));
  }
</style>
