<template>
  <div class="container">
    <Breadcrumb :items="['menu.finance', 'menu.finance.bill', 'menu.finance.bill.orderSummary']" />

    <a-card class="general-card" style="padding-top:30px">

      <a-table
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        :data="renderData"
        :bordered="false"
        label-align="center"
        @page-change="handlePageChange"
        @page-size-change="handlePageSizeChange"
      >
        <template #columns>
          <a-table-column title="订单编号" data-index="id" />
          <a-table-column title="客户姓名" data-index="customer_name" />
          <a-table-column title="借款金额">
            <template #cell="{ record }">
              ¥{{ record.disbursement_amount }}
            </template>
          </a-table-column>
          <a-table-column title="期数" data-index="period" />
          <a-table-column title="已还/总期">
            <template #cell="{ record }">
              {{ record.paid_period }}/{{ record.total_period }}
            </template>
          </a-table-column>
          <a-table-column title="待还金额">
            <template #cell="{ record }">
              ¥{{ record.remaining_amount }}
            </template>
          </a-table-column>
          <a-table-column title="已还金额">
            <template #cell="{ record }">
              ¥{{ record.paid_amount }}
            </template>
          </a-table-column>
          <a-table-column title="下期还款时间" data-index="next_repayment_date" />
          <a-table-column title="状态" data-index="status" />
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { 
  getFinanceOrderList,
  type FinanceOrder,
  type FinanceOrderQuery
} from '@/api/finance';
import { Message } from '@arco-design/web-vue';
import { useRoute } from 'vue-router';
import Breadcrumb from '@/components/breadcrumb/index.vue';

const route = useRoute();

const customer = route.query.customer_name?.toString() || '';

// 搜索表单
const searchForm = reactive<FinanceOrderQuery>({
  page: 1,
  pageSize: 20,
  order_no: '',
  customer_name: customer
});

// 表格数据
const renderData = ref<FinanceOrder[]>([]);
const loading = ref(false);

// 分页配置
const pagination = reactive({
  current: 1,
  pageSize: 20,
  total: 0,
  showTotal: true,
  showJumper: true,
  showPageSize: true
});

// 获取数据
const fetchData = async () => {
  loading.value = true;
  try {
    const params = {
      ...searchForm,
      page: pagination.current,
      pageSize: pagination.pageSize
    };
    const response = await getFinanceOrderList(params);
    if (response.data && response.code === 20000) {
      renderData.value = (response.data as any)?.list || [];
      pagination.total = (response.data as any)?.total || 0;
    } else {
      Message.error(response.data?.msg || '获取数据失败');
    }
  } catch (error) {
    Message.error('获取数据失败');
  } finally {
    loading.value = false;
  }
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  fetchData();
};

// 分页变化
const handlePageChange = (page: number) => {
  pagination.current = page;
  fetchData();
};

// 每页数量变化
const handlePageSizeChange = (size: number) => {
  pagination.pageSize = size;
  pagination.current = 1;
  fetchData();
};

// 单笔还款
const handleSingleRepayment = (record: FinanceOrder) => {
  Message.info(`单笔还款 - 订单: ${record.order_no}`);
  // 这里可以打开还款弹窗或跳转到还款页面
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
  .arco-btn-size-small{padding:0px 4px;}
</style>