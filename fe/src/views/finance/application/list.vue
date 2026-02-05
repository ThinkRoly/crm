<template>
  <div class="container">
    <Breadcrumb :items="['menu.finance', 'menu.finance.application']" />

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
                <a-form-item field="channel" label="对接渠道">
                  <a-select
                    v-model="searchForm.channel"
                    placeholder="请选择渠道"
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
                <a-form-item field="salesperson" label="业务员">
                  <a-select
                    v-model="searchForm.salesperson"
                    placeholder="请选择业务员"
                    allow-clear
                  >
                    <a-option
                        v-for="item in userOptions"
                        :key="item.value"
                        :value="item.value"
                    >{{ item.label }}</a-option
                    >
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="sign_date" label="签单日期">
                  <a-date-picker
                      v-model="formData.sign_date"
                      placeholder="请选择签单日期"
                      format="YYYY-MM-DD"
                      style="width: 100%"
                  />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="repayment_date" label="还款日期">
                  <a-date-picker
                      v-model="formData.repayment_date"
                      placeholder="请选择还款日期"
                      format="YYYY-MM-DD"
                      style="width: 100%"
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
              新增进件
            </a-button>
            <a-popconfirm
              content="确认删除选中的进件吗？"
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
          <a-table-column title="对接渠道" data-index="channel" />
          <a-table-column title="签单日期">
            <template #cell="{ record }">
              {{ formatDate(record.sign_date) }}
            </template>
          </a-table-column>
          <a-table-column title="业务员" data-index="salesperson" />
          <a-table-column title="还款日期" data-index="repayment_date" />
          <a-table-column title="公证" data-index="notarization" />
          <a-table-column title="签约金额">
            <template #cell="{ record }">
              ¥{{ record.contract_amount }}
            </template>
          </a-table-column>
          <a-table-column title="合同点位" data-index="contract_rate" />
          <a-table-column title="应收金额">
            <template #cell="{ record }">
              ¥{{ record.receivable_amount }}
            </template>
          </a-table-column>
          <a-table-column title="买断金额">
            <template #cell="{ record }"> ¥{{ record.buyout_amount }} </template>
          </a-table-column>
          <a-table-column title="保证金">
            <template #cell="{ record }"> ¥{{ record.deposit }} </template>
          </a-table-column>
          <a-table-column title="释放金额">
            <template #cell="{ record }"> ¥{{ record.deposit }} </template>
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
                <a-button type="text" size="small" @click="disbursement(record)"
                >出款</a-button
                >
                <a-popconfirm
                  content="确认删除该进件吗？"
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

    <!-- 编辑/新增弹窗 -->
    <a-modal
      v-model:visible="modalVisible"
      :title="modalTitle"
      :mask-closable="false"
      :footer="false"
      width="800px"
      @cancel="handleModalCancel"
    >
      <EditForm
          v-if="modalVisible"
          :initial-data="formData"
          :is-edit="!!formData.id"
          :is-view-mode="isViewMode"
          :city-options="cityOptions"
          :channel-options="channelOptions"
          :user-options="userOptions"
          @save="handleSave"
          @cancel="handleModalCancel"
      />
    </a-modal>

    <a-modal
        v-model:visible="disbursementModalVisible"
        :title="disbursementModalTitle"
        :mask-closable="false"
        :footer="false"
        width="800px"
        @cancel="handleDisbursementModalCancel"
    >
      <DisbursementForm
          v-if="disbursementModalVisible"
          :initial-data="disbursementFormData"
          :is-edit="!!formData.id"
          :is-view-mode="isViewMode"
          :city-options="cityOptions"
          :channel-options="channelOptions"
          :user-options="userOptions"
          @save="handleSave"
          @cancel="handleModalCancel"
      />
    </a-modal>
  </div>
</template>

<script setup lang="ts">
  import { ref, reactive, onMounted } from 'vue';
  import {
    getFinanceApplicationList,
    createFinanceApplication,
    updateFinanceApplication,
    deleteFinanceApplication,
    type FinanceApplication,
    type FinanceApplicationQuery,
    type Option, FinanceDisbursement,
  } from '@/api/finance';
  import { Message } from '@arco-design/web-vue';
  import Breadcrumb from '@/components/breadcrumb/index.vue';
  import DisbursementForm from "@/views/finance/disbursement/DisbursementForm.vue";
  import EditForm from './EditForm.vue';

  // 搜索表单
  const searchForm = reactive<FinanceApplicationQuery>({
    page: 1,
    pageSize: 20,
    customer_name: '',
  });

  // 选项数据
  const cityOptions = ref<Option[]>([]);

  const channelOptions = ref<Option[]>([]);

  const userOptions = ref<Option[]>([]);

  // 表格数据
  const renderData = ref<FinanceApplication[]>([]);
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
  const isViewMode = ref(false);
  // 弹窗相关
  const modalVisible = ref(false);
  const modalTitle = ref('');
  const formData = ref<Partial<FinanceApplication>>({
    id: undefined,
    customer_name: '',
    city: '',
    channel: '',
    sign_date: '',
    salesperson: '',
    repayment_date: '',
    notarization: '否',
    contract_amount: 0,
    contract_rate: '10%',
    receivable_amount: 0,
    buyout_amount: 0,
    deposit: 0,
    release_amount: 0,
    rebate_rate: '10%',
    rebate_amount: 0,
    commission_rate: '10%',
    commission_fee: 0,
    risk_control_person: '',
    debt_settlement_amount: 0,
    department: '',
    household: '',
    education: '',
    company_full_name: '',
    company_type: '',
    housing_fund_base: '0',
    salary: '0',
    operation_date: '',
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
      const response = await getFinanceApplicationList(params);
      if ((response as any).code === 20000) {
        const data = response.data as any;
        renderData.value = data?.list || [];
        pagination.total = data?.total || 0;

        // 更新选项数据
        if (data?.cityOptions) {
          cityOptions.value = data.cityOptions;
        }
        if (data?.channelOptions) {
          channelOptions.value = data.channelOptions;
        }
        if (data?.userOptions) {
          userOptions.value = data.userOptions;
        }
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

  // 新增进件
  const handleAdd = () => {
    modalTitle.value = '新增进件';
    formData.value = {
      id: undefined,
      customer_name: '',
      city: '',
      channel: '',
      sign_date: '',
      salesperson: '',
      repayment_date: '',
      notarization: '否',
      contract_amount: 0,
      contract_rate: '10%',
      receivable_amount: 0,
      buyout_amount: 0,
      deposit: 0,
      release_amount: 0,
      rebate_rate: '10%',
      rebate_amount: 0,
      commission_rate: '10%',
      commission_fee: 0,
      risk_control_person: '',
      debt_settlement_amount: 0,
      department: '',
      household: '',
      education: '',
      company_full_name: '',
      company_type: '',
      housing_fund_base: '0',
      salary: '0',
      operation_date: '',
      remark: '',
    };
    modalVisible.value = true;
  };

  // 编辑进件
  const handleEdit = (record: FinanceApplication) => {
    modalTitle.value = '编辑进件';
    formData.value = { ...record };
    modalVisible.value = true;
  };

  // 查看进件
  const handleView = (_record: FinanceApplication) => {
    modalTitle.value = '查看进件';
    formData.value = { ..._record };
    modalVisible.value = true;
    isViewMode.value = true;
  };

  const disbursementModalVisible = ref(false);
  const disbursementModalTitle = ref('');
  const disbursementFormData = ref<Partial<FinanceDisbursement>>({});
  const disbursement = (record: FinanceApplication) => {
    disbursementModalTitle.value = '新增出款';
    disbursementFormData.value = {
      channel: record.channel,
      customer_name: record.customer_name,
      city: record.city,
      application_id: record.id,
    };
    disbursementModalVisible.value = true;
  };

  // 删除进件
  const handleDelete = async (id: number) => {
    try {
      const response = await deleteFinanceApplication(id);
      if (response.data && response.code === 20000) {
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
      Message.warning('请先选择要删除的进件');
      return;
    }

    try {
      // 这里调用批量删除API，如果后端没有提供批量删除接口，则逐个删除
      const deletePromises = selectedRows.value.map((id) =>
        deleteFinanceApplication(id)
      );
      await Promise.all(deletePromises);
      Message.success('批量删除成功');
      selectedRows.value = [];
      fetchData();
    } catch (error) {
      Message.error('批量删除失败');
    }
  };

  // 处理表单保存
  const handleSave = async (data: Partial<FinanceApplication>) => {
    try {
      // 确保必填字段有值
      const requestData = {
        ...data,
        customer_name: data.customer_name || '',
        city: data.city || '',
        channel: data.channel || '',
        sign_date: data.sign_date || '',
        salesperson: data.salesperson || '',
        repayment_date: data.repayment_date || '',
        notarization: data.notarization || '否',
        contract_amount: data.contract_amount || 0,
        contract_rate: data.contract_rate || '10%',
        receivable_amount: data.receivable_amount || 0,
        buyout_amount: data.buyout_amount || 0,
        deposit: data.deposit || 0,
        release_amount: data.release_amount || 0,
        rebate_rate: data.rebate_rate || '10%',
        rebate_amount: data.rebate_amount || 0,
        commission_rate: data.commission_rate || '10%',
        commission_fee: data.commission_fee || 0,
        risk_control_person: data.risk_control_person || '',
        debt_settlement_amount: data.debt_settlement_amount || 0,
        department: data.department || '',
        household: data.household || '',
        education: data.education || '',
        company_full_name: data.company_full_name || '',
        company_type: data.company_type || '',
        housing_fund_base: data.housing_fund_base || '0',
        salary: data.salary || '0',
        remark: data.remark || '',
      } as FinanceApplication;

      if (data.id) {
        // 更新
        const response = await updateFinanceApplication(data.id, requestData);
        if (response.data && response.code === 20000) {
          Message.success('更新成功');
        } else {
          Message.error(response.data?.msg || '更新失败');
          return;
        }
      } else {
        // 创建
        const response = await createFinanceApplication(requestData);
        if (response.data && response.code === 20000) {
          Message.success('创建成功');
        } else {
          Message.error(response.data?.msg || '创建失败');
          return;
        }
      }

      modalVisible.value = false;
      fetchData();
    } catch (error) {
      Message.error('保存失败');
    }
  };

  // 处理弹窗取消
  const handleModalCancel = () => {
    modalVisible.value = false;
  };
  const handleDisbursementModalCancel = () => {
    disbursementModalVisible.value = false;
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
