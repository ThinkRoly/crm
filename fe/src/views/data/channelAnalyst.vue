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
                    :multiple="true"
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
                    <a-radio value="5">当月</a-radio>
                    <a-radio value="8">当年</a-radio>
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
      <a-row>
        <a-col :span="6">
          <a-table
            row-key="id"
            :loading="loading"
            :pagination="pagination"
            :data="renderData"
            :bordered="false"
            label-align="center"
          >
            <template #columns>
              <a-table-column title="渠道名称" data-index="name" />
              <a-table-column title="总量" data-index="cnt" />
            </template>
          </a-table>
        </a-col>
        <a-col :span="18">
          <Chart height="310px" :option="chartOption" />
        </a-col>
      </a-row>
    </a-card>
  </div>
</template>

<script lang="ts" setup>
  import { ref, reactive } from 'vue';
  import useLoading from '@/hooks/loading';
  import useChartOption from '@/hooks/chart-option';
  import { Pagination } from '@/types/global';
  import { ChannelInfo, queryChannelAnalystList, ChannelListParams } from '@/api/data';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import { useAppStore } from '@/store';
  import ChannelDetail from './channelDetail.vue';

  const { loading, setLoading } = useLoading(true);
  const userStore = useAppStore();
  const renderData = ref<ChannelInfo[]>([]);
  const aaaaaa = ref([]);
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
      const { data } = await queryChannelAnalystList(params);
      timeType.value.timeType = formModel.value.timeType;
      timeType.value.time = formModel.value.time;
      renderData.value = data.list;
      aaaaaa.value = data.followUserList;
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
      'source[]': formModel.value.source,
      'times[]': formModel.value.time,
    } as unknown as ChannelListParams);
  };
  search();

  const { chartOption } = useChartOption((isDark) => {
    // echarts support https://echarts.apache.org/zh/theme-builder.html
    // It's not used here
    return {
      legend: {
        left: 'center',
        data: ['纯文本', '图文类', '视频类'],
        bottom: 0,
        icon: 'circle',
        itemWidth: 8,
        textStyle: {
          color: isDark ? 'rgba(255, 255, 255, 0.7)' : '#4E5969',
        },
        itemStyle: {
          borderWidth: 0,
        },
      },
      tooltip: {
        show: true,
        trigger: 'item',
      },
      graphic: {
        elements: [
          {
            type: 'text',
            left: 'center',
            top: '40%',
            style: {
              text: '',
              textAlign: 'center',
              fill: isDark ? '#ffffffb3' : '#4E5969',
              fontSize: 14,
            },
          },
          {
            type: 'text',
            left: 'center',
            top: '50%',
            style: {
              text: '',
              textAlign: 'center',
              fill: isDark ? '#ffffffb3' : '#1D2129',
              fontSize: 16,
              fontWeight: 500,
            },
          },
        ],
      },
      series: [
        {
          type: 'pie',
          radius: ['50%', '70%'],
          center: ['50%', '50%'],
          label: {
            formatter: '{b}-{c} ({d}%)',
            fontSize: 14, 
            color: isDark ? 'rgba(255, 255, 255, 0.7)' : '#4E5969',
          },
          itemStyle: {
            borderColor: isDark ? '#232324' : '#fff',
            borderWidth: 1,
          },
          data: aaaaaa.value,
        },
      ],
    };
  });
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
