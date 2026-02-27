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
        @page-change="handlePageChange"
        @page-size-change="handlePageSizeChange"
      >
        <template #columns>
          <a-table-column title="订单编号" data-index="disbursement_id" />
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
                <a-button
                  v-if="record.status !== 'completed'"
                  type="text"
                  size="small"
                  @click="handleRepay(record)"
                >
                  还款
                </a-button>
              </a-space>
            </template>
          </a-table-column>
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script setup lang="ts">
import { Message, Modal } from '@arco-design/web-vue';
import { ref, reactive, onMounted } from 'vue';
import { 
  getFinanceRepaymentPlan,
  repay,
  type FinanceRepaymentPlan,
  type FinanceRepaymentPlanQuery,
  type RepaymentRequest
} from '@/api/finance';
import Breadcrumb from '@/components/breadcrumb/index.vue';
import { useRoute } from 'vue-router';

// 路由参数
const route = useRoute();

// 基本信息
const basicInfo = reactive<Array<{label: string, value: string}>>([
  { label: '客户姓名', value: '' },
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
        if (basicInfo[3]) basicInfo[3].value = `${firstRecord.total_period} 期` || '';
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
const handlePageChange = (page: number) => {
  pagination.current = page;
  fetchData();
};
// 还款操作
const handleRepay = async (record: FinanceRepaymentPlan) => {
  try {
    const remainingAmount = record.due_amount ;
    const today = new Date().toISOString().split('T')[0];

    // 确认对话框
    await Modal.confirm({
      title: '确认还款',
      content: `确认为客户 ${record.customer_name} 的第${record.period}期账单还款 ¥${remainingAmount} 吗？`,
      okText: '确认还款',
      cancelText: '取消',
      onOk: async () => {
        const repaymentData: RepaymentRequest = {
          plan_id: record.id!,
          repayment_amount: remainingAmount,
          repayment_date: today
        };

        const response = await repay(repaymentData);
        if (response.data && response.code === 20000) {
          // 更新本地数据
          const index = repaymentPlanData.value.findIndex(item => item.id === record.id);
          if (index !== -1) {
            repaymentPlanData.value[index] = {
              ...repaymentPlanData.value[index],
              ...response.data.plan
            };
          }
          Message.success(`${response.data.message}，剩余待还：¥${response.data.repayment_info.remaining_amount}`);
        } else {
          Message.error(response.data?.msg || '还款失败');
        }
      }
    });
  } catch (error: any) {
    console.error('还款处理错误:', error);
    Message.error(error.message || '还款处理失败');
  }
};


const getStatusColor = (status: string) => {
  switch (status) {
    case 'pending': return 'blue';
    case 'completed': return 'green';
    case 'overdue': return 'red';
    case 'partial': return 'orange';
    default: return 'gray';
  }
};

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    pending: '待还款',
    completed: '已还款',
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