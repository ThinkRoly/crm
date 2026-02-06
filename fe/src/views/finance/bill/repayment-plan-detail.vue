<template>
  <div class="container">
    <Breadcrumb :items="['menu.finance', 'menu.finance.bill', 'menu.finance.bill.repaymentPlanDetail']" />

    <a-card class="general-card" style="padding-top:30px">
      <!-- 基本信息卡片 -->
      <a-row :gutter="24" style="margin-bottom: 24px">
        <a-col :span="8">
          <a-descriptions :data="basicInfo" :column="1" :bordered="true" title="基本信息">
            <template #label="{ item }">
              {{ item?.label || '' }}
            </template>
          </a-descriptions>
        </a-col>
        <a-col :span="8">
          <a-descriptions :data="loanInfo" :column="1" :bordered="true" title="借款信息">
            <template #label="{ item }">
              {{ item?.label || '' }}
            </template>
          </a-descriptions>
        </a-col>
        <a-col :span="8">
          <a-descriptions :data="repaymentInfo" :column="1" :bordered="true" title="还款信息">
            <template #label="{ item }">
              {{ item?.label || '' }}
            </template>
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
              ¥{{ record.remaining_amount }}
            </template>
          </a-table-column>
          <a-table-column title="状态" data-index="status" />
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

// 借款信息
const loanInfo = reactive<Array<{label: string, value: string}>>([
  { label: '借款金额', value: '' },
  { label: '年化利率', value: '' },
  { label: '月还款额', value: '' },
  { label: '还款方式', value: '' },
]);

// 还款信息
const repaymentInfo = reactive<Array<{label: string, value: string}>>([
  { label: '已还期数', value: '' },
  { label: '已还金额', value: '' },
  { label: '待还金额', value: '' },
  { label: '逾期天数', value: '' },
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
        if (basicInfo[1]) basicInfo[1].value = firstRecord.order_id || '';
        if (basicInfo[2]) basicInfo[2].value = firstRecord.sign_date || '';
        if (basicInfo[3]) basicInfo[3].value = `${firstRecord.total_period}期` || '';
        
        // 安全更新借款信息
        if (loanInfo[0]) loanInfo[0].value = `¥${firstRecord.loan_amount}` || '';
        if (loanInfo[1]) loanInfo[1].value = `${firstRecord.annual_rate}%` || '';
        if (loanInfo[2]) loanInfo[2].value = `¥${firstRecord.monthly_repayment_amount}` || '';
        if (loanInfo[3]) loanInfo[3].value = firstRecord.repayment_method || '';
        
        // 安全更新还款信息
        if (repaymentInfo[0]) repaymentInfo[0].value = `${firstRecord.paid_period}期` || '';
        if (repaymentInfo[1]) repaymentInfo[1].value = `¥${firstRecord.paid_amount}` || '';
        if (repaymentInfo[2]) repaymentInfo[2].value = `¥${firstRecord.remaining_amount}` || '';
        if (repaymentInfo[3]) repaymentInfo[3].value = `${firstRecord.overdue_days}天` || '';
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
const handleRepay = (record: FinanceRepaymentPlan) => {
  Message.info(`还款 - 期数: ${record.period}, 金额: ¥${record.due_amount}`);
  // 这里可以打开还款弹窗或跳转到还款页面
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