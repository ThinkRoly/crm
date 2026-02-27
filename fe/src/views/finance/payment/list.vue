<template>
  <div class="container">
    <Breadcrumb :items="['menu.finance', 'menu.finance.payment']" />

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
                    <a-option
                      v-for="item in cityOptions"
                      :key="item.value"
                      :value="item.value"
                      >{{ item.label }}</a-option
                    >
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="repayment_type" label="回款类型">
                  <a-select
                    v-model="searchForm.repayment_type"
                    placeholder="请选择回款类型"
                    allow-clear
                  >
                    <a-option value="垫资费用">垫资费用</a-option>
                    <a-option value="保证金">保证金</a-option>
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
          <a-table-column title="对接渠道" data-index="channel" />
          <a-table-column title="城市" data-index="city" />
          <a-table-column title="签单日期">
            <template #cell="{ record }">
              {{ formatDate(record.sign_date) }}
            </template>
          </a-table-column>
          <a-table-column title="回款金额">
            <template #cell="{ record }">
              ¥{{ record.repayment_amount }}
            </template>
          </a-table-column>
          <a-table-column title="回款日期">
            <template #cell="{ record }">
              {{ formatDate(record.repayment_date) }}
            </template>
          </a-table-column>
          <a-table-column title="回款类型" data-index="repayment_type" />
          <a-table-column title="通道点位">
            <template #cell="{ record }">
              {{ record.channel_point }}%
            </template>
          </a-table-column>
          <a-table-column title="通道费用">
            <template #cell="{ record }"> ¥{{ record.channel_amount }} </template>
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
        <a-row :gutter="16">
          <a-col :span="12">
            <a-form-item label="进件编号">
              <a-select
                v-model="formData.application_id"
                placeholder="请选择进件"
              >
                <a-option
                  v-for="option in applicationOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </a-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="客户姓名">
              <a-input v-model="formData.customer_name" readonly />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="对接渠道">
              <a-input v-model="formData.channel" readonly />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="城市">
              <a-input v-model="formData.city" readonly />
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item
          field="sign_date"
          label="签单日期"
          :rules="[{ required: true, message: '请选择签单日期' }]"
        >
          <a-date-picker
            v-model="formData.sign_date"
            placeholder="请选择签单日期"
            format="YYYY-MM-DD"
            style="width: 100%"
          />
        </a-form-item>

        <a-form-item
          field="repayment_amount"
          label="回款金额"
          :rules="[
            { required: true, message: '请输入回款金额' },
            { pattern: /^\\d+(\\.\\d{1,2})?$/, message: '请输入正确的金额' },
          ]"
        >
          <a-input-number
            v-model="formData.repayment_amount"
            placeholder="请输入回款金额"
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>

        <a-form-item
          field="repayment_date"
          label="回款日期"
          :rules="[{ required: true, message: '请选择回款日期' }]"
        >
          <a-date-picker
            v-model="formData.repayment_date"
            placeholder="请选择回款日期"
            format="YYYY-MM-DD"
            style="width: 100%"
          />
        </a-form-item>

        <a-form-item
          field="repayment_type"
          label="回款类型"
          :rules="[{ required: true, message: '请选择回款类型' }]"
        >
          <a-select
            v-model="formData.repayment_type"
            placeholder="请选择回款类型"
          >
            <a-option value="垫资费用">垫资费用</a-option>
            <a-option value="保证金">保证金</a-option>
          </a-select>
        </a-form-item>

        <a-form-item
          field="channel_point"
          label="通道点位"
          :rules="[{ required: true, message: '请选择通道点位' }]"
        >
          <a-select
            v-model="formData.channel_point"
            placeholder="请选择通道点位"
          >
            <a-option value="0.03">0.03</a-option>
            <a-option value="0.035">0.035</a-option>
            <a-option value="0.05">0.05</a-option>
          </a-select>
        </a-form-item>

        <a-form-item field="channel_amount" label="通道费用">
          <a-input
            v-model="formData.channel_amount"
            disabled
            placeholder="自动计算"
          />
        </a-form-item>

        <a-form-item field="salesperson" label="业务员">
          <a-select v-model="formData.salesperson" placeholder="请选择业务员">
            <a-option
              v-for="item in userOptions"
              :key="item.value"
              :value="item.value"
              >{{ item.label }}</a-option
            >
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
  import { ref, reactive, onMounted, watch } from 'vue';
  import {
    getFinancePaymentList,
    createFinancePayment,
    updateFinancePayment,
    deleteFinancePayment,
    Option,
    type FinancePayment,
  } from '@/api/finance';
  import { Message } from '@arco-design/web-vue';
  import Breadcrumb from '@/components/breadcrumb/index.vue';

  // 下拉选项数据
  const channelOptions = ref<Option[]>([]);
  const cityOptions = ref<Option[]>([]);
  const applicationOptions = ref<Option[]>([]);
  const userOptions = ref<Option[]>([]);

  // 搜索表单
  const searchForm = reactive({
    page: 1,
    pageSize: 20,
    customer_name: '',
    channel: '',
    city: '',
    repayment_type: '',
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
  const formData = ref<FinancePayment>({
    id: undefined,
    application_id: 0,
    customer_name: '',
    channel: '',
    city: '',
    sign_date: '',
    repayment_amount: 0,
    repayment_date: '',
    repayment_type: '',
    channel_point: '',
    channel_amount: 0,
    salesperson: '',
    remark: '',
  });

  // 计算通道费用
  const calculateChannelFee = () => {
    if (formData.value.repayment_amount && formData.value.channel_point) {
      // 通道费用 = 回款金额 * 通道点位
      formData.value.channel_amount = Number(
        (
          formData.value.repayment_amount *
          parseFloat(formData.value.channel_point)
        ).toFixed(2)
      );
    }
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
      const response = await getFinancePaymentList(params);
      if ((response as any).code === 20000) {
        const data = response.data as any;
        if (data?.applicationOptions) {
          applicationOptions.value = data.applicationOptions;
        }
        if (data?.userOptions) {
          userOptions.value = data.userOptions;
        }
        if (data?.cityOptions) {
          cityOptions.value = data.cityOptions;
        }
        if (data?.channelOptions) {
          channelOptions.value = data.channelOptions;
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

  // 查看回款
  const handleView = (_record: any) => {
    // 这里可以打开查看弹窗或跳转到详情页
    Message.info('查看回款功能');
  };

  watch(
    () => formData.value.application_id,
    (newVal) => {
      if (newVal) {
        const app = applicationOptions.value.find((opt) => opt.value === newVal);
        if (app) {
          formData.value.customer_name = app.customer_name || '';
          formData.value.city = app.city || '';
          formData.value.channel = app.channel || '';
        }
      }
    }
  );

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
