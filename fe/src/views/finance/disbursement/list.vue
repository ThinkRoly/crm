<template>
  <div class="container">
    <Breadcrumb :items="['menu.finance', 'menu.finance.disbursement']" />

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
                <a-form-item field="customer_name" label="客户姓名">
                  <a-input
                    v-model="searchForm.customer_name"
                    placeholder="请输入客户姓名"
                  />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="disbursement_type" label="出款类型">
                  <a-select
                    v-model="searchForm.disbursement_type"
                    placeholder="请选择出款类型"
                    allow-clear
                  >
                    <a-option value="垫资费用">垫资费用</a-option>
                    <a-option value="保证金">保证金</a-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="6">
                <a-form-item field="account" label="出款账户">
                  <a-select
                    v-model="searchForm.account"
                    placeholder="请选择出款账户"
                    allow-clear
                  >
                    <a-option
                      v-for="item in accountOptions"
                      :key="item.value"
                      :value="item.value"
                      >{{ item.label }}</a-option
                    >
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="interest_rate" label="出款利率">
                  <a-select
                    v-model="searchForm.interest_rate"
                    placeholder="请选择出款利率"
                    allow-clear
                  >
                    <a-option value="0.4">0.4</a-option>
                    <a-option value="0.5">0.5</a-option>
                    <a-option value="0.6">0.6</a-option>
                    <a-option value="0.7">0.7</a-option>
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
            <a-button type="primary" status="success" @click="handleAdd">
              <template #icon>
                <icon-plus />
              </template>
              新增出款
            </a-button>
            <a-popconfirm
              content="确认删除选中的出款记录吗？"
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
          <a-table-column title="客户姓名" data-index="customer_name" />
          <a-table-column title="城市" data-index="city" />
          <a-table-column title="签单日期">
            <template #cell="{ record }">
              {{ formatDate(record.sign_date) }}
            </template>
          </a-table-column>
          <a-table-column title="出款金额">
            <template #cell="{ record }">
              ¥{{ record.disbursement_amount }}
            </template>
          </a-table-column>
          <a-table-column title="出款类型" data-index="disbursement_type" />
          <a-table-column title="出款日期">
            <template #cell="{ record }">
              {{ formatDate(record.outbound_date) }}
            </template>
          </a-table-column>
          <a-table-column title="出款账户" data-index="account" />
          <a-table-column title="出款利率" data-index="interest_rate" />
          <a-table-column title="操作">
            <template #cell="{ record }">
              <a-space>
                <a-button type="text" size="small" @click="handleEdit(record)"
                  >编辑</a-button
                >
                <a-button type="text" size="small" @click="handleView(record)"
                  >查看</a-button
                >
                <a-popconfirm
                  content="确认删除该出款记录吗？"
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

    <!-- 弹窗部分新增 -->
    <a-modal
        v-model:visible="modalVisible"
        :title="disbursementModalTitle"
        :mask-closable="false"
        :footer="false"
        width="800px"
        @cancel="handleModalCancel"
    >
      <DisbursementForm
          v-if="modalVisible"
          :initial-data="formData"
          :is-edit="!!formData.id"
          :is-view-mode="isViewMode"
          :applicationOptions="applicationOptions"
          :city-options="cityOptions"
          :channel-options="channelOptions"
          :user-options="userOptions"
          @save="handleSubmit"
          @cancel="handleModalCancel"
      />
    </a-modal>

  </div>
</template>

<script setup lang="ts">
  import { ref, reactive, onMounted } from 'vue';
  import {
    getFinanceDisbursementList,
    createFinanceDisbursement,
    updateFinanceDisbursement,
    deleteFinanceDisbursement, type Option,
  } from '@/api/finance';
  import { Message } from '@arco-design/web-vue';
  import Breadcrumb from '@/components/breadcrumb/index.vue';
  import DisbursementForm from "@/views/finance/disbursement/DisbursementForm.vue";

  const accountOptions = ref([]);


  // 搜索表单
  const searchForm = reactive({
    page: 1,
    pageSize: 20,
    customer_name: '',
    channel: '',
    city: '',
    disbursement_type: '',
    sign_date: null,
    account: '',
    interest_rate: '',
  });

  // 表格数据
  const renderData = ref<any[]>([]);
  const loading = ref(false);
  const selectedRows = ref<number[]>([]);
  const applicationOptions = ref<Option[]>([]);

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
    customer_name: '',
    city: '',
    channel: '',
    sign_date: '',
    disbursement_amount: 0,
    disbursement_type: '',
    period: 1,
    outbound_date: '',
    account: '',
    interest_rate: '',
    monthly_repayment_amount: 0,
    channel_point: '',
    channel_fee: 0,
    salesperson: '',
    remark: '',
  });

  // 计算月还款金额和通道费用
  const calculateAmounts = () => {
    if (formData.value.disbursementAmount && formData.value.interestRate) {
      // 月还款金额 = 出款金额 * 利率
      formData.value.monthlyRepaymentAmount = Number(
        (
          formData.value.disbursementAmount *
          parseFloat(formData.value.interestRate)
        ).toFixed(2)
      );
    }

    if (formData.value.disbursementAmount && formData.value.channelPoint) {
      // 通道费用 = 出款金额 * 通道点位
      formData.value.channel_fee = Number(
        (
          formData.value.disbursementAmount *
          parseFloat(formData.value.channel_point)
        ).toFixed(2)
      );
    }
  };

  // 监听金额和利率变化，自动计算
  const updateCalculatedFields = () => {
    calculateAmounts();
  };

  // 获取数据
  const fetchData = async () => {
    loading.value = true;
    try {
      const params = {
        ...searchForm,
        page: pagination.current,
        pageSize: pagination.pageSize,
      };
      const response = await getFinanceDisbursementList(params);
      if ((response as any).code === 20000) {
        const data = response.data as any;
        if (data?.applicationOptions) {
          applicationOptions.value = data.applicationOptions;
        }
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

  // 重置
  const handleReset = () => {
    searchForm.customer_name = '';
    searchForm.channel = '';
    searchForm.city = '';
    searchForm.disbursement_type = '';
    searchForm.sign_date = null;
    searchForm.account = '';
    searchForm.interest_rate = '';
    pagination.current = 1;
    fetchData();
  };

  // 选择行变化
  const handleSelectionChange = (_keys: number[]) => {
    // 选择行变化处理函数
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

  // 新增出款
  const handleAdd = () => {
    modalTitle.value = '新增出款';
    formData.value = {
      id: undefined,
      customer_name: '',
      city: '',
      channel: '',
      sign_date: '',
      disbursement_amount: 0,
      disbursement_type: '',
      period: 1,
      outbound_date: '',
      account: '',
      interest_rate: '',
      monthly_repayment_amount: 0,
      channel_point: '',
      channel_fee: 0,
      salesperson: '',
      remark: '',
    };
    modalVisible.value = true;
  };

  // 编辑出款
  const handleEdit = (record: any) => {
    modalTitle.value = '编辑出款';
    formData.value = { ...record };
    modalVisible.value = true;
  };

  // 查看出款
  const handleView = (_record: any) => {
    // 这里可以打开查看弹窗或跳转到详情页
    Message.info('查看出款功能');
  };

  // 删除出款
  const handleDelete = async (id: number) => {
    try {
      const response = await deleteFinanceDisbursement(id);
      if (response.data && response.data.code === 20000) {
        Message.success('删除成功');
        fetchData();
      } else {
        Message.error(response.data?.msg || '删除失败');
      }
    } catch (error) {
      Message.error('删除失败');
    }
  };

  // 批量删除
  const handleBatchDelete = async () => {
    if (!selectedRows.value.length) {
      Message.warning('请先选择要删除的出款记录');
      return;
    }

    try {
      // 使用Promise.all批量处理，避免在循环中使用await
      const deletePromises = selectedRows.value.map((id) =>
        deleteFinanceDisbursement(id)
      );
      await Promise.all(deletePromises);
      Message.success('批量删除成功');
      selectedRows.value = [];
      fetchData();
    } catch (error) {
      Message.error('批量删除失败');
    }
  };

  // 提交表单
  const handleSubmit = async () => {
    try {
      // 在提交前确保计算字段是最新的
      calculateAmounts();

      if (formData.value.id) {
        // 更新
        const response = await updateFinanceDisbursement(
          formData.value.id,
          formData.value
        );
        if (response.data && response.data.code === 20000) {
          Message.success('更新成功');
        } else {
          Message.error(response.data?.msg || '更新失败');
          return;
        }
      } else {
        // 创建
        const response = await createFinanceDisbursement(formData.value);
        if (response.data && response.data.code === 20000) {
          Message.success('创建成功');
        } else {
          Message.error(response.data?.msg || '创建失败');
          return;
        }
      }

      modalVisible.value = false;
      fetchData();
    } catch (error) {
      console.error(error);
      Message.error('保存失败');
    }
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
</style>
