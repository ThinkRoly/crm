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
        <a-form-item label="出款类型" field="disbursement_type" :rules="[{ required: true, message: '请选择出款类型' }]">
          <a-select v-model="formData.disbursement_type" placeholder="请选择类型">
            <a-option value="贷款">贷款</a-option>
            <a-option value="分期付款">分期付款</a-option>
          </a-select>
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">

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
      <a-col :span="12">
        <a-form-item label="期数" field="period" :rules="[{ required: true, message: '请选择期数' }]">
          <a-select v-model="formData.period" placeholder="请选择期数">
            <a-option value="1">1</a-option>
            <a-option value="2">2</a-option>
            <a-option value="3">3</a-option>
            <a-option value="4">4</a-option>
            <a-option value="5">5</a-option>
            <a-option value="6">6</a-option>
            <a-option value="7">7</a-option>
            <a-option value="8">8</a-option>
            <a-option value="9">9</a-option>
            <a-option value="10">10</a-option>
            <a-option value="11">11</a-option>
            <a-option value="12">12</a-option>
          </a-select>
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="出款日期" field="disbursement_date" :rules="[{ required: true, message: '请输入出款日期' }]">
          <a-date-picker
            v-model="formData.disbursement_date"
            placeholder="请选择出款日期"
            format="YYYY-MM-DD"        style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="出款账户" field="account" :rules="[{ required: true, message: '请选择出款账户' }]">
          <a-select v-model="formData.account" placeholder="请选择出款账户">
            <a-option
                v-for="option in props.accountOptions"
                :key="option.value"
                :value="option.value"
            >
              {{ option.label }}
            </a-option>
          </a-select>
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">

      <a-col :span="12">
        <a-form-item label="利率" field="interest_rate" :rules="[{ required: true, message: '请输入利率' }]">
          <a-input-number
              v-model="formData.interest_rate"
              :readonly="readonly"
              placeholder="请输入出款利率(1-100)"
              :min="0"
              :max="100"
              :precision="2"            style="width: 100%"
              @change="calculateAutoFields"
            />%
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="月还款额" field="monthly_repayment_amount" >
          <a-input-number
            v-model="formData.monthly_repayment_amount"
            readOnly
            :disabled="true"
            :min="0"
            :precision="2"        style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="通道点位">
          <a-input-number
            v-model="formData.channel_point"
            :readonly="readonly"
            placeholder="请输入通道点位(1-100)"
            :min="0"
            :max="100"
            :precision="2"            style="width: 100%"
            @change="calculateAutoFields"
          />%
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="channel_amount" label="通道金额">
          <a-input-number
            v-model="formData.channel_amount"
            readOnly
            placeholder="自动计算"
            :disabled="true"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item label="业务员">
          <a-select v-model="formData.salesperson" placeholder="请选择业务员">
            <a-option
              v-for="option in props.userOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </a-option>
          </a-select>
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
import { reactive, watch, onMounted, computed, nextTick } from 'vue';
import type {FinanceDisbursement, Option} from '@/api/finance';

const props = withDefaults(defineProps<{
  initialData?: Partial<FinanceDisbursement>;
  isEdit?: boolean;
  isViewMode?: boolean;
  applicationOptions?: Option[];
  userOptions?: Option[];
  accountOptions?: Option[];
}>(), {
  isEdit: false,
  applicationOptions: () => [],
  userOptions: () => [],
  accountOptions: () => [],
});

// 表单数据（合并初始数据 + 默认值）
const formData = reactive<FinanceDisbursement>({
  id: undefined,
  application_id: null,
  customer_name: '',
  channel: '',
  city: '',
  sign_date: '',
  period: 0,
  disbursement_type: '',
  disbursement_amount: 0,
  account: '',
  interest_rate: 0,
  monthly_repayment_amount: 0,
  channel_point: 0,
  channel_amount: 0,
  salesperson: null,
  remark: '',
  disbursement_date: undefined,
  ...props.initialData,
});

// 计算属性 - 判断是否为只读模式
const readonly = computed(() => props.isViewMode);

// 自动计算相关字段
const calculateAutoFields = () => {
  const disbursement_amount = Number(formData.disbursement_amount) || 0;

  // 计算通道金额 = 出款金额 × 通道点位
  const channel_point = Number(formData.channel_point) || 0;
  formData.channel_amount = Number((disbursement_amount * channel_point / 100).toFixed(2));

  // 计算月还款额 = 出款金额 × 利率
  const interest_rate = Number(formData.interest_rate) || 0;
  formData.monthly_repayment_amount = Number((disbursement_amount * interest_rate / 100).toFixed(2));
};

// 监听初始数据变化（编辑时）
watch(
  () => props.initialData,
  (newVal) => {
    if (newVal) {
      Object.assign(formData, newVal);
      // 编辑时重新计算自动字段
      nextTick(() => {
        calculateAutoFields();
      });
    }
  },
  { immediate: true }
);

// 监听关键字段变化，实时计算
watch([() => formData.disbursement_amount, () => formData.interest_rate, () => formData.channel_point],
  () => {
    calculateAutoFields();
  }
);

// 组件挂载时初始化计算
onMounted(() => {
  if (props.isEdit && props.initialData) {
    calculateAutoFields();
  }
});
</script>

<style scoped lang="less">

</style>
