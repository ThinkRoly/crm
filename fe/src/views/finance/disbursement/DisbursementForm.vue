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
        <a-form-item label="出款金额" field="amount" :rules="[{ required: true, message: '请输入出款金额' }]">
          <a-input-number
            v-model="formData.amount"
            placeholder="请输入出款金额"
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="出款日期" field="disbursement_date">
          <a-date-picker
            v-model="formData.disbursement_date"
            placeholder="请选择出款日期"
            format="YYYY-MM-DD"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="收款账户" field="account_name">
          <a-input v-model="formData.account_name" placeholder="请输入收款人姓名" />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="银行名称" field="bank_name">
          <a-input v-model="formData.bank_name" placeholder="请输入银行名称" />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="银行卡号" field="bank_account">
          <a-input v-model="formData.bank_account" placeholder="请输入银行卡号" />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="用途说明" field="purpose">
          <a-textarea v-model="formData.purpose" placeholder="请输入出款用途" :max-length="200" />
        </a-form-item>
      </a-col>
    </a-row>

    <!-- 状态与备注 -->
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="操作人" field="operator">
          <a-input v-model="formData.operator" placeholder="请输入操作人" />
        </a-form-item>
      </a-col>
    </a-row>

    <a-form-item label="备注" field="remark">
      <a-textarea v-model="formData.remark" placeholder="请输入备注" />
    </a-form-item>

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
import type {FinanceApplication, FinanceDisbursement, Option} from '@/api/finance'; // 假设你有此类型定义

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
  disbursement_date: '',
  account_name: '',
  bank_name: '',
  bank_account: '',
  purpose: '',
  status: 'pending',
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
  if (formData.amount <= 0) {
    console.warn('出款金额必须大于 0');
    return;
  }
  emit('save', { ...formData });
};

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
/* 可选：微调样式 */
</style>
