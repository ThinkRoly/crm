<template>
  <div class="container bigtable">
    <Breadcrumb :items="['menu.data', 'menu.data.my']" />

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
              <a-col :span="5">
                <a-form-item field="name" label="团队">
                  <a-select
                    v-model="formModel.team_id"
                    :options="teamList"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="name" label="客户来源">
                  <a-select v-model="formModel.userFrom" :options="userFromOptions" placeholder="请选择" :allow-clear="true" />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="name" label="报表类型">
                  <a-select v-model="formModel.reportType" :options="reportTypeOptions" placeholder="请选择" />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="name" label="时间">
                  <a-date-picker v-model="formModel.time1" v-if="formModel.reportType=='week'"
                    :disabledDate="(current:any) => dayjs(current).day() != 1"
                  />
                  <a-month-picker v-model="formModel.time2" v-if="formModel.reportType=='month'"/>
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
          <a-table-column title="跟进顾问" data-index="name" />
          <a-table-column
            title="总计"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="all_num"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '', '', record.follow_user_id)
                "
                >{{ record.all_num }}</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="0星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star0"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '0', '', record.follow_user_id)
                "
                >{{ record.star0_num }} <br/> ({{ record.star0 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="1星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star1"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '1', '', record.follow_user_id)
                "
                >{{ record.star1_num }} <br/> ({{ record.star1 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="2星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star2"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '2', '', record.follow_user_id)
                "
                >{{ record.star2_num }} <br/> ({{ record.star2 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="3星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star3"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '3', '', record.follow_user_id)
                "
                >{{ record.star3_num }} <br/> ({{ record.star3 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="4星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star4"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '4', '', record.follow_user_id)
                "
                >{{ record.star4_num }} <br/> ({{ record.star4 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="5星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star5"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '5', '', record.follow_user_id)
                "
                >{{ record.star5_num }} <br/> ({{ record.star5 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="2星以上"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star6"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '6', '', record.follow_user_id)
                "
                >{{ record.star6_num }} <br/> ({{ record.star6 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="3星以上"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star7"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '7', '', record.follow_user_id)
                "
                >{{ record.star7_num }} <br/> ({{ record.star7 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="4星以上"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star8"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '8', '', record.follow_user_id)
                "
                >{{ record.star8_num }} <br/> ({{ record.star8 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="未跟进"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="status0_num"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '', '0', record.follow_user_id)
                "
                >{{ record.status0_num }} <br/> ({{ record.status0 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="已跟进"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="status1_num"
          >
            <template #cell="{ record }">
              <a-link
                @click="
                  showDetail(record.channel, '', '99', record.follow_user_id)
                "
                >{{ record.status1_num }} <br/> ({{ record.status1 }})</a-link
              >
            </template>
          </a-table-column>
        </template>
      </a-table>
    </a-card>
    <a-modal
      v-model:visible="showModal"
      :width="900"
      :footer="false"
      style="padding: 0"
    >
      <template #title> 渠道详情 </template>
      <MyDetail ref="detail" />
    </a-modal>
  </div>
</template>

<script lang="ts" setup>
  import { ref, reactive } from 'vue';
  import dayjs from 'dayjs';
  import useLoading from '@/hooks/loading';
  import { Pagination } from '@/types/global';
  import {
    queryReportList
  } from '@/api/data';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import { useAppStore } from '@/store';
  import MyDetail from './myDetail.vue';

  console.log(dayjs().day())

  const { loading, setLoading } = useLoading(true);
  const userStore = useAppStore();
  const renderData = ref([]);
  const lastMonth = ((new Date()).getMonth() === 0) ? 12 : (new Date()).getMonth();
  const lastMonthStr = lastMonth < 10 ? [0, lastMonth].join("") : lastMonth;
  const lastMonthYear = ((new Date()).getMonth() === 0) ? (new Date()).getFullYear() - 1:  (new Date()).getFullYear();
  const userFrom = ref(1);
  const formModel = ref({
    user_id: '',
    team_id: '',
    userFrom: 1,
    time1: '',
    time2: [lastMonthYear, lastMonthStr].join("-"),
    reportType: 'month',
  });
  const userFromOptions = ref([]);
  const reportTypeOptions = ref([{'label':'月报表', 'value':'month'}, {'label':'周报表', 'value':'week'}]);
  const basePagination: Pagination = { current: 1, pageSize: 20 };
  const pagination = reactive({ ...basePagination });
  const showModal = ref(false);
  const detail = ref();
  const teamList = ref<SelectOptionData[]>([]);
  const userList = ref<SelectOptionData[]>([]);

  const showDetail = (
    channel: string,
    star: string,
    status: string,
    followUserId: string
  ) => {
    if (userStore.$state.buttonPermission.includes('ReportDataDetail')) {
      showModal.value = true;
      detail.value.fetchDataAll(
        star,
        status,
        formModel.value.reportType,
        formModel.value.time1,
        formModel.value.time2,
        followUserId,
        userFrom
      );
    }
  };
  const fetchData = async (
    params: {
      current: 1,
      pageSize: 20
    }
  ) => {
    setLoading(true);
    try {
      const { data } = await queryReportList(params);
      userFromOptions.value = data.userFromList;
      renderData.value = data.list;
      pagination.current = params.current;
      pagination.total = data.total;
      teamList.value = data.allTeamListSelect;
      userList.value = data.followUserList;
      userFrom.value = formModel.value.userFrom;
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
    } as unknown as any);
  };
  const onPageChange = (current: number) => {
    fetchData({
      ...basePagination,
      current,
    } as any);
  };

  search();

  const download = () => {
    const u = new URLSearchParams({
      ...basePagination,
      ...formModel.value,
    } as unknown as string[][]).toString();
    (window as Window).location = `/api/data/reportlist?export=1&${u}`;
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
.arco-link{
  color:rgb(var(--gray-10))
}
</style>
