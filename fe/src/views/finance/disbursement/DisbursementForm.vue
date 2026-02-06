<template>
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
          <a-select v-model="formData.application_id" placeholder="请选择进件">
            <a-option
                v-for="option in props.applicationOptions"
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
          <a-input v-model="formData.customer_name" readonly/>
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="对接渠道">
          <a-input v-model="formData.channel" readonly/>
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="城市">
          <a-input v-model="formData.city" readonly />
        </a-form-item>
      </a-col>
    </a-row>

    <!-- 出款核心字段 -->
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="签约日期" field="sign_date" :rules="[{ required: true, message: '请输入签约日期' }]">
          <a-date-picker
            v-model="formData.sign_date"
            placeholder="请选择签约日期"
            format="YYYY-MM-DD"        style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="期数" field="period" :rules="[{ required: true, message: '请输入期数' }]">
          <a-input-number
            v-model="formData.period"
            :min="1"        style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="出款类型" field="disbursement_type" :rules="[{ required: true, message: '请选择出款类型' }]">
          <a-select v-model="formData.disbursement_type" placeholder="请选择类型">
            <a-option value="loan">贷款</a-option>
            <a-option value="installment">分期付款</a-option>
          </a-select>
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="出款金额" field="disbursement_amount" :rules="[{ required: true, message: '请输入出款金额' }]">
          <a-input-number
            v-model="formData.disbursement_amount"
            placeholder="请输入出款金额"
            mode="button"
            :min="0"
            :precision="2"        style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="收款账户" field="account" :rules="[{ required: true, message: '请输入银行卡号' }]">
          <a-input v-model="formData.account" placeholder="请输入银行卡号" />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="利率(%)" field="interest_rate" :rules="[{ required: true, message: '请输入利率' }]">
          <a-input-number
            v-model="formData.interest_rate"
            :min="0"
            :precision="2"
            suffix="%"        style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="月还款额" field="monthly_repayment_amount" :rules="[{ required: true, message: '请输入月还款额' }]">
          <a-input-number
            v-model="formData.monthly_repayment_amount"
            :min="0"
            :precision="2"        style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="通道点位">
          <a-input v-model="formData.channel_point" placeholder="可选" />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="业务员">
          <a-input v-model="formData.salesperson" placeholder="可选" />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="备注">
          <a-textarea v-model="formData.remark" placeholder="请输入备注" />
        </a-form-item>
      </a-col>
    </a-row>

    <!-- 操作按钮 -->
    <a-form-item>
      <a-space size="medium" style="float: right;">
        <a-button @click="$emit('cancel')">取消</a-button>
        <a-button type="primary" @click="handleSubmit">保存</a-button>
      </a-space>
    </a-form-item>
  </a-form>
</template>

<script setup lang="ts">
import { reactive, watch, onMounted } from 'vue';
import type {FinanceApplication, FinanceDisbursement, Option} from '@/api/finance';

const props = withDefaults(defineProps<{
  initialData?: Partial<FinanceDisbursement>;
  isEdit?: boolean;
  isViewMode?: boolean;
  applicationOptions?: Option[];
}>(), {
  isEdit: false,
  applicationOptions: () => [],
});

// 表单数据（合并初始数据 + 默认值）
const formData = reactive<FinanceDisbursement>({
  id: undefined,
  application_id: undefined,
  customer_name: '',
  channel: '',
  city: '',
  amount: 0,
  disbursement_amount: 0,
  disbursement_date: '',
  account_name: '',
  bank_name: '',
  bank_account: '',
  purpose: '',
  operator: '',
  remark: '',
  ...props.initialData,
});

// Emit 事件
const emit = defineEmits<{
  (e: 'cancel'): void;
  (e: 'save', data: FinanceDisbursement): void;
}>();

// 提交处理
const handleSubmit = () => {
  if (formData.disbursement_amount <= 0) {
    console.warn('出款金额必须大于 0');
    return;
  }
  emit('save', { ...formData });
};

watch(() => formData.application_id, (newVal) => {
  if (newVal) {
    const app = props.applicationOptions.find(opt => opt.value === newVal);
    if (app) {
      formData.customer_name = app.customer_name || '';
      formData.city = app.city || '';
      formData.channel = app.channel || '';
    }
  }
});

watch(
  () => props.initialData,
  (newVal) => {
    if (newVal) {
      Object.assign(formData, newVal);
    }
  },
  { immediate: true }
);
</script>

<style scoped lang="less">

</style>
