<template>
  <div class="container">
    <Breadcrumb :items="['menu.finance', 'menu.finance.bill']" />

    <a-card class="general-card" style="padding-top: 30px">
      <a-row>
        <a-col :flex="1">
          <a-form
            :model="searchForm"
            :label-col-props="{ span: 7 }"
            :wrapper-col-props="{ span: 17 }"
            label-align="left"
          >
            <a-row :gutter="16">
              <a-col :span="6">
                <a-form-item field="customerName" label="客户姓名">
                  <a-input
                    v-model="searchForm.customerName"
                    placeholder="请输入客户姓名"
                  />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="channel" label="对接渠道">
                  <a-select
                    v-model="searchForm.channel"
                    placeholder="请选择对接渠道"
                    allow-clear
                  >
                    <a-option
                      v-for="item in channelOptions"
                      :key="item.value"
                      :value="item.value"
                      >{{ item.label }}</a-option
                    >
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="city" label="城市">
                  <a-select
                    v-model="searchForm.city"
                    placeholder="请选择城市"
                    allow-clear
                  >
                    <a-option value="厦门">厦门</a-option>
                    <a-option value="杭州">杭州</a-option>
                    <a-option value="武汉">武汉</a-option>
                  </a-select>
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
            <a-button type="primary" @click="handleSearch">
              <template #icon>
                <icon-search />
              </template>
              查询
            </a-button>
            <a-popconfirm
              content="确认删除选中的账单吗？"
              type="warning"
              @ok="handleBatchDelete"
            >
              <a-button
                type="primary"
                status="danger"
                :disabled="!selectedRows.length"
              >
                <template #icon>
                  <icon-delete />
                </template>
                批量删除
              </a-button>
            </a-popconfirm>
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
        @page-change="handlePageChange"
        @page-size-change="handlePageSizeChange"
      >
        <template #columns>
          <a-table-column title="序号" :width="80">
            <template #cell="{ rowIndex }">
              {{
                rowIndex + 1 + (pagination.current - 1) * pagination.pageSize
              }}
            </template>
          </a-table-column>
          <a-table-column title="客户信息">
            <template #cell="{ record }">
              <div class="customer-info">
                <div class="customer-name">{{ record.customer_name }}</div>
                <div class="customer-id">ID: {{ record.customer_id }}</div>
                <div class="customer-phone">{{ record.customer_phone }}</div>
              </div>
            </template>
          </a-table-column>
          <a-table-column title="借款情况">
            <template #cell="{ record }">
              <div class="loan-info">
                <div class="loan-amount">¥{{ record.loan_amount }}</div>
                <div class="loan-count">{{ record.loan_count }}笔借款</div>
              </div>
            </template>
          </a-table-column>
          <a-table-column title="还款进度">
            <template #cell="{ record }">
              <div class="repayment-progress">
                <div class="progress-bar">
                  <div
                    class="progress-fill"
                    :style="{ width: record.repayment_progress + '%' }"
                  ></div>
                </div>
                <div class="progress-text">
                  已还 ¥{{ record.repaid_amount }}
                  <span class="percentage"
                    >{{ record.repayment_progress }}%</span
                  >
                </div>
              </div>
            </template>
          </a-table-column>
          <a-table-column title="待还金额">
            <template #cell="{ record }">
              <div class="amount-info">
                <div class="total-due">¥{{ record.total_due_amount }}</div>
                <div class="interest">含利息 ¥{{ record.interest_amount }}</div>
              </div>
            </template>
          </a-table-column>
          <a-table-column title="逾期情况">
            <template #cell="{ record }">
              <div class="overdue-info">
                <div
                  :class="{ overdue: record.overdueStatus !== '无逾期' }"
                  class="overdue-status"
                >
                  {{ record.overdue_status }}
                </div>
                <div v-if="record.overdueAmount > 0" class="overdue-amount">
                  金额: ¥{{ record.overdue_amount }}
                </div>
              </div>
            </template>
          </a-table-column>
          <a-table-column title="最后还款">
            <template #cell="{ record }">
              <div class="last-repayment">
                <div class="last-date">{{
                  formatDate(record.last_repayment_date)
                }}</div>
                <div class="last-amount">¥{{ record.last_repayment_amount }}</div>
              </div>
            </template>
          </a-table-column>
          <a-table-column title="操作">
            <template #cell="{ record }">
              <a-space>
                <a-button type="text" size="small" @click="handleEdit(record)"
                  >编辑</a-button
                >
                <a-button type="text" size="small" @click="handleView(record)"
                  >查看</a-button
                >
                <a-button
                  type="text"
                  size="small"
                  @click="handleLoanOrderSummary(record)"
                  >借款订单汇总</a-button
                >
                <a-button
                  type="text"
                  size="small"
                  @click="handleRepaymentPlanDetail(record)"
                  >还款计划详情</a-button
                >
                <a-popconfirm
                  content="确认删除该账单吗？"
                  type="warning"
                  @ok="handleDelete(record.id)"
                >
                  <a-button type="text" size="small">删除</a-button>
                </a-popconfirm>
              </a-space>
            </template>
          </a-table-column>
        </template>
      </a-table>
    </a-card>

    <!-- 编辑弹窗 -->
    <a-modal
      v-model:visible="modalVisible"
      :title="modalTitle"
      :mask-closable="false"
      :footer="false"
      width="600px"
    >
      <a-form
        :model="formData"
        :label-col-props="{ span: 6 }"
        :wrapper-col-props="{ span: 18 }"
        size="large"
        auto-label-width
      >
        <a-form-item
          field="billNumber"
          label="账单编号"
          :rules="[{ required: true, message: '请输入账单编号' }]"
        >
          <a-input
            v-model="formData.billNumber"
            :disabled="!!formData.id"
            placeholder="请输入账单编号"
          />
        </a-form-item>

        <a-form-item
          field="customerName"
          label="客户名称"
          :rules="[{ required: true, message: '请输入客户名称' }]"
        >
          <a-input
            v-model="formData.customerName"
            placeholder="请输入客户名称"
          />
        </a-form-item>

        <a-form-item
          field="amount"
          label="金额"
          :rules="[
            { required: true, message: '请输入金额' },
            { pattern: /^\d+(\.\d{1,2})?$/, message: '请输入正确的金额' },
          ]"
        >
          <a-input-number
            v-model="formData.amount"
            placeholder="请输入金额"
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>

        <a-form-item
          field="due_date"
          label="到期日期"
          :rules="[{ required: true, message: '请选择到期日期' }]"
        >
          <a-date-picker
            v-model="formData.due_date"
            placeholder="请选择到期日期"
            format="YYYY-MM-DD"
            style="width: 100%"
          />
        </a-form-item>

        <a-form-item
          field="status"
          label="状态"
          :rules="[{ required: true, message: '请选择状态' }]"
        >
          <a-select v-model="formData.status" placeholder="请选择状态">
            <a-option value="pending">待处理</a-option>
            <a-option value="paid">已支付</a-option>
            <a-option value="overdue">逾期</a-option>
          </a-select>
        </a-form-item>

        <a-form-item
          field="paymentStatus"
          label="支付状态"
          :rules="[{ required: true, message: '请选择支付状态' }]"
        >
          <a-select
            v-model="formData.paymentStatus"
            placeholder="请选择支付状态"
          >
            <a-option value="unpaid">未支付</a-option>
            <a-option value="partial">部分支付</a-option>
            <a-option value="paid">已支付</a-option>
          </a-select>
        </a-form-item>

        <a-form-item field="remark" label="备注">
          <a-textarea v-model="formData.remark" placeholder="请输入备注" />
        </a-form-item>

        <a-form-item>
          <a-space size="medium" style="float: right">
            <a-button @click="modalVisible = false">取消</a-button>
            <a-button type="primary" @click="handleSubmit">确定</a-button>
          </a-space>
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
  import { ref, reactive, onMounted } from 'vue';
  import { getFinanceBillList } from '@/api/finance';
  import { Message } from '@arco-design/web-vue';
  import Breadcrumb from '@/components/breadcrumb/index.vue';
  import { useRouter } from 'vue-router';

  // 路由实例
  const router = useRouter();

  // 下拉选项数据
  const channelOptions = ref([
    { value: '渠道A', label: '渠道A' },
    { value: '渠道B', label: '渠道B' },
    { value: '渠道C', label: '渠道C' },
  ]);

  // 搜索表单
  const searchForm = reactive({
    page: 1,
    pageSize: 20,
    customerName: '',
    channel: '',
    city: '',
  });

  // 表格数据
  const renderData = ref<any[]>([]);
  const loading = ref(false);
  const selectedRows = ref<number[]>([]);

  // 分页配置
  const pagination = reactive({
    current: 1,
    pageSize: 20,
    total: 0,
    showTotal: true,
    showJumper: true,
    showPageSize: true,
  });

  // 弹窗相关
  const modalVisible = ref(false);
  const modalTitle = ref('');
  const formData = ref<any>({
    id: undefined,
    customerName: '',
    customer_id: '',
    customer_phone: '',
    channel: '',
    city: '',
    loan_amount: 0,
    loan_count: 0,
    repaid_amount: 0,
    repayment_progress: 0,
    total_due_amount: 0,
    interest_amount: 0,
    overdue_status: '无逾期',
    overdue_amount: 0,
    last_repayment_date: '',
    last_repayment_amount: 0,
    remark: '',
  });

  // 获取数据
  const fetchData = async () => {
    loading.value = true;
    try {
      const params = {
        ...searchForm,
        page: pagination.current,
        pageSize: pagination.pageSize,
      };
      const response = await getFinanceBillList(params);
      if ((response as any).code === 20000) {
        const data = response.data as any;
        renderData.value = data?.list || [];
        pagination.total = data?.total || 0;
      } else {
        Message.error((response as any).msg || '获取数据失败-');
      }
    } catch (error) {
      Message.error('获取数据失败');
    } finally {
      loading.value = false;
    }
  };

  // 格式化日期
  const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
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

  // 批量删除
  const handleBatchDelete = async () => {
    if (!selectedRows.value.length) {
      Message.warning('请先选择要删除的账单');
      return;
    }
    
    try {
      // 这里调用批量删除API
      Message.success('批量删除成功');
      selectedRows.value = [];
      fetchData();
    } catch (error) {
      Message.error('批量删除失败');
    }
  };

  // 编辑账单
  const handleEdit = (record: any) => {
    formData.value = { ...record };
    modalTitle.value = '编辑账单';
    modalVisible.value = true;
  };

  // 查看账单
  const handleView = (record: any) => {
    formData.value = { ...record };
    modalTitle.value = '查看账单';
    modalVisible.value = true;
  };

  // 删除账单
  const handleDelete = async (id: number) => {
    try {
      // 这里调用删除API
      Message.success('删除成功');
      fetchData();
    } catch (error) {
      Message.error('删除失败');
    }
  };

  // 提交表单
  const handleSubmit = async () => {
    try {
      if (formData.value.id) {
        // 更新逻辑
        Message.success('更新成功');
      } else {
        // 创建逻辑
        Message.success('创建成功');
      }
      modalVisible.value = false;
      fetchData();
    } catch (error) {
      Message.error('保存失败');
    }
  };

  // 新增的处理函数
  const handleLoanOrderSummary = (record: any) => {
    // 借款订单汇总功能 - 跳转到借款订单汇总页面
    router.push({ name: 'FinanceBillOrderSummary' });
  };

  const handleRepaymentPlanDetail = (record: any) => {
    // 还款计划详情功能 - 跳转到还款计划详情页面
    router.push({
      name: 'FinanceBillRepaymentPlanDetail',
      query: { orderId: record.orderNo },
    });
  };

  onMounted(() => {
    fetchData();
  });
</script>

<style scoped lang="less">
  .container {
    padding: 0 20px 20px 20px;
  }

  .arco-btn-size-small {
    padding: 0 4px;
  }

  .customer-info {
    .customer-name {
      font-weight: bold;
    }

    .customer-id {
      color: #666;
      font-size: 12px;
    }

    .customer-phone {
      color: #666;
      font-size: 12px;
    }
  }

  .loan-info {
    .loan-amount {
      font-weight: bold;
    }

    .loan-count {
      color: #666;
      font-size: 12px;
    }
  }

  .repayment-progress {
    .progress-bar {
      height: 8px;
      margin-bottom: 4px;
      background-color: #f0f0f0;
      border-radius: 4px;

      .progress-fill {
        height: 100%;
        background-color: #4caf50;
        border-radius: 4px;
      }
    }

    .progress-text {
      font-size: 12px;

      .percentage {
        color: #4caf50;
        font-weight: bold;
      }
    }
  }

  .amount-info {
    .total-due {
      font-weight: bold;
    }

    .interest {
      color: #666;
      font-size: 12px;
    }
  }

  .overdue-info {
    .overdue-status {
      &.overdue {
        color: #f44336;
        font-weight: bold;
      }
    }

    .overdue-amount {
      color: #f44336;
      font-size: 12px;
    }
  }

  .last-repayment {
    .last-date {
      font-weight: bold;
    }

    .last-amount {
      color: #666;
      font-size: 12px;
    }
  }
</style>
