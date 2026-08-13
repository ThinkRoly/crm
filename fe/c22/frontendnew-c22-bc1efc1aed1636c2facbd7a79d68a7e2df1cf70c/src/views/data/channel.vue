<template>
  <div class="container bigtable">
    <Breadcrumb :items="['menu.data', 'menu.data.channel']" />

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
                <a-form-item field="name" label="渠道名称">
                  <a-select
                    v-model="formModel.source"
                    :options="sourceOptions"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="18">
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
          <a-table-column title="渠道名称" data-index="name" />
          <a-table-column
            title="总计"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="all_num"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '', '')">{{
                record.all_num
              }}</a-link>
            </template>
          </a-table-column>
          <a-table-column
            title="0星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star0"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '0', '')"
                >{{ record.star0_num }}  <br/>({{ record.star0 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="1星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star1"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '1', '')"
                >{{ record.star1_num }}  <br/>({{ record.star1 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="2星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star2"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '2', '')"
                >{{ record.star2_num }}  <br/>({{ record.star2 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="3星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star3"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '3', '')"
                >{{ record.star3_num }}  <br/>({{ record.star3 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="4星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star4"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '4', '')"
                >{{ record.star4_num }}  <br/>({{ record.star4 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="5星"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star5"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '5', '')"
                >{{ record.star5_num }}  <br/>({{ record.star5 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="2星以上"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star6"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '6', '')"
                >{{ record.star6_num }}  <br/>({{ record.star6 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="3星以上"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star7"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '7', '')"
                >{{ record.star7_num }}  <br/>({{ record.star7 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="4星以上"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="star8"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '8', '')"
                >{{ record.star8_num }}  <br/>({{ record.star8 }})</a-link
              >
            </template>
          </a-table-column>
          <a-table-column
            title="未跟进"
            :sortable="{ sortDirections: ['ascend', 'descend'] }"
            data-index="status0_num"
          >
            <template #cell="{ record }">
              <a-link @click="showDetail(record.channel, '', '1')"
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
              <a-link @click="showDetail(record.channel, '', '1')"
                >{{ record.status1_num }}  <br/>({{ record.status1 }})</a-link
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
      <ChannelDetail ref="detail" />
    </a-modal>
  </div>
</template>

<script lang="ts" setup>
  import { ref, reactive } from 'vue';
  import useLoading from '@/hooks/loading';
  import { Pagination } from '@/types/global';
  import { ChannelInfo, queryChannelList, ChannelListParams } from '@/api/data';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import { useAppStore } from '@/store';
  import ChannelDetail from './channelDetail.vue';

  const { loading, setLoading } = useLoading(true);
  const userStore = useAppStore();
  const renderData = ref<ChannelInfo[]>([]);
  const formModel = ref({
    source: '',
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
  const basePagination: Pagination = { current: 1, pageSize: 20 };
  const pagination = reactive({ ...basePagination });
  const showModal = ref(false);
  const detail = ref();
  const sourceOptions = ref<SelectOptionData[]>([]);

  const showDetail = (channel: string, star: string, status: string) => {
    if (userStore.$state.buttonPermission.includes('ChannelDataDetail')) {
      showModal.value = true;
      detail.value.fetchDataAll(
        channel,
        star,
        status,
        formModel.value.timeType,
        formModel.value.time,
        ''
      );
    }
  };
  const fetchData = async (
    params: ChannelListParams = {
      current: 1,
      pageSize: 20,
      timeType: '3',
      type: '',
    }
  ) => {
    setLoading(true);
    try {
      params.timeType = formModel.value.timeType;
      const { data } = await queryChannelList(params);
      timeType.value.timeType = formModel.value.timeType;
      timeType.value.time = formModel.value.time;
      renderData.value = data.list;
      pagination.current = params.current;
      pagination.total = data.total;
      sourceOptions.value = data.sourceList;
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
      type: '',
    });
  };

  const download = () => {
    const u = new URLSearchParams({
      ...basePagination,
      ...formModel.value,
    } as unknown as string[][]).toString();
    (window as Window).location = `/api/channel/list?export=1&${u}&times[]=${formModel.value.time[0]}&times[]=${formModel.value.time[1]}`;
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
  .bigtable .arco-link{
    color:rgb(var(--gray-10))
  }
</style>
