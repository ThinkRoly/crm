<template>
  <div class="container">
    <Breadcrumb :items="['menu.finance', 'menu.finance.bill', 'menu.finance.bill.repaymentPlanDetail']" />

    <a-card class="general-card" style="padding-top:30px">
      <!-- 基本信息卡片 -->
      <a-row :gutter="24" style="margin-bottom: 24px">
        <a-col :span="8">
          <a-descriptions :data="basicInfo" :column="1" :bordered="true" title="基本信息">

          </a-descriptions>
        </a-col>
      </a-row>

      <!-- 还款计划表格 -->
      <a-table
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        :data="repaymentPlanData"
        :bordered="false"
        label-align="center"
      >
        <template #columns>
          <a-table-column title="期数" data-index="period" />
          <a-table-column title="应还日期" data-index="due_date" />
          <a-table-column title="应还金额">
            <template #cell="{ record }">
              ¥{{ record.due_amount }}
            </template>
          </a-table-column>
          <a-table-column title="已还金额">
            <template #cell="{ record }">
              ¥{{ record.paid_amount }}
            </template>
          </a-table-column>
          <a-table-column title="待还金额">
            <template #cell="{ record }">
              ¥{{ record.due_amount - record.paid_amount }}
            </template>
          </a-table-column>
          <a-table-column title="状态" data-index="status">
            <template #cell="{ record }">
              <a-tag
                :color="getStatusColor(record.status)"
                size="small"
              >
                {{ getStatusText(record.status) }}
              </a-tag>
            </template>
          </a-table-column>

          <a-table-column title="操作">
            <template #cell="{ record }">
              <a-space>
                <a-button type="text" size="small" @click="handleRepay(record)">还款</a-button>
              </a-space>
            </template>
          </a-table-column>
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { 
  getFinanceRepaymentPlan,
  type FinanceRepaymentPlan,
  type FinanceRepaymentPlanQuery
} from '@/api/finance';
import { Message } from '@arco-design/web-vue';
import Breadcrumb from '@/components/breadcrumb/index.vue';
import { useRoute } from 'vue-router';

// 路由参数
const route = useRoute();

// 基本信息
const basicInfo = reactive<Array<{label: string, value: string}>>([
  { label: '客户姓名', value: '' },
  { label: '订单编号', value: '' },
  { label: '签约日期', value: '' },
  { label: '总期数', value: '' },
]);


// 表格数据
const repaymentPlanData = ref<FinanceRepaymentPlan[]>([]);
const loading = ref(false);

// 分页配置
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showTotal: true,
  showJumper: true,
  showPageSize: true
});

// 获取数据
const fetchData = async () => {
  loading.value = true;
  try {
    const customer = route.query.customer_name?.toString() || '';

    const params: FinanceRepaymentPlanQuery = {
      customer_name: customer,
      page: pagination.current,
      pageSize: pagination.pageSize
    };
    
    const response = await getFinanceRepaymentPlan(params);
    if (response.data && response.code === 20000) {
      const responseData = response.data as any;
      repaymentPlanData.value = responseData?.list || [];
      pagination.total = responseData?.total || 0;
      
      // 更新基本信息
      if (responseData?.list && responseData.list.length > 0) {
        const firstRecord = responseData.list[0];
        
        // 安全更新基本信息
        if (basicInfo[0]) basicInfo[0].value = firstRecord.customer_name || '';
        if (basicInfo[1]) basicInfo[1].value = firstRecord.disbursement_id || '';
        if (basicInfo[2]) basicInfo[2].value = firstRecord.sign_date || '';
        if (basicInfo[3]) basicInfo[3].value = `${firstRecord.total_period}期` || '';
      }
    } else {
      Message.error(response.data?.msg || '获取数据失败');
    }
  } catch (error) {
    Message.error('获取数据失败');
  } finally {
    loading.value = false;
  }
};

// 还款操作
const handleRepay = async (record: FinanceRepaymentPlan) => {
  try {
    // 这里应该调用还款接口
    // const response = await payRepayment(record.id, { amount: record.due_amount });

    // 模拟更新本地数据状态
    const index = repaymentPlanData.value.findIndex(item => item.id === record.id);
    if (index !== -1) {
      // 根据还款金额更新状态
      if (record.paid_amount >= record.due_amount) {
        repaymentPlanData.value[index].status = 'paid';
      } else if (record.paid_amount > 0) {
        repaymentPlanData.value[index].status = 'partial';
      }

      // 更新已还金额
      repaymentPlanData.value[index].paid_amount = record.due_amount;
    }

    Message.success('还款成功');
  } catch (error) {
    Message.error('还款失败');
  }
};

const getStatusColor = (status: string) => {
  switch (status) {
    case 'pending': return 'blue';
    case 'paid': return 'green';
    case 'overdue': return 'red';
    case 'partial': return 'orange';
    default: return 'gray';
  }
};

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    pending: '待还款',
    paid: '已还款',
    overdue: '逾期',
    partial: '部分还款'
  };
  return statusMap[status] || status;
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
  .arco-btn-size-small{padding:0px 4px;}
  
  .arco-descriptions {
    .arco-descriptions-item-label {
      width: 100px;
      font-weight: normal;
    }
  }
</style>