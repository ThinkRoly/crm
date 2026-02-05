<template>
  <a-form 
    :model="formData" 
    :label-col-props="{ span: 6 }" 
    :wrapper-col-props="{ span: 18 }"
    size="large"
    auto-label-width
  >
    <!-- 基础信息 -->
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="customerName" label="客户姓名">
          <a-input v-model="formData.customer_name" placeholder="请输入客户姓名" />
        </a-form-item>
      </a-col>
    </a-row>
    
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="city" label="城市">
          <a-select v-model="formData.city" placeholder="请选择城市">
            <a-option 
              v-for="option in props.cityOptions" 
              :key="option.value" 
              :value="option.value"
            >
              {{ option.label }}
            </a-option>
          </a-select>
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="channel" label="对接渠道">
          <a-input v-model="formData.channel" placeholder="请输入对接渠道" />
        </a-form-item>
      </a-col>
    </a-row>
    
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="salesperson" label="业务员">
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
        <a-form-item field="notarization" label="公证">
          <a-select v-model="formData.notarization" placeholder="请选择是否公证">
            <a-option value="是">是</a-option>
            <a-option value="否">否</a-option>
          </a-select>
        </a-form-item>
      </a-col>
    </a-row>
    
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="department" label="部门">
          <a-input v-model="formData.department" placeholder="请输入部门" />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="household" label="户籍">
          <a-input v-model="formData.household" placeholder="请输入户籍" />
        </a-form-item>
      </a-col>
    </a-row>
    
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="education" label="学历">
          <a-input v-model="formData.education" placeholder="请输入学历" />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="companyFullName" label="单位全称">
          <a-input v-model="formData.company_full_name" placeholder="请输入单位全称" />
        </a-form-item>
      </a-col>
    </a-row>
    
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="companyType" label="企业类型">
          <a-input v-model="formData.company_type" placeholder="请输入企业类型" />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="housingFundBase" label="公积金基数">
          <a-input-number 
            v-model="formData.housing_fund_base" 
            placeholder="请输入公积金基数" 
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>
    
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="salary" label="代发工资">
          <a-input-number 
            v-model="formData.salary" 
            placeholder="请输入代发工资" 
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="risk_control_person" label="风控人员">
          <a-select v-model="formData.risk_control_person" placeholder="请选择风控人员">
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
    </a-row>
    
    <!-- 日期字段 -->
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="sign_date" label="签单日期">
          <a-date-picker 
            v-model="formData.sign_date" 
            placeholder="请选择签单日期"
            format="YYYY-MM-DD"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
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
    
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="operation_date" label="操作日期">
          <a-date-picker 
            v-model="formData.operation_date" 
            placeholder="请选择操作日期"
            format="YYYY-MM-DD"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="submit_date" label="提交日期">
          <a-date-picker 
            v-model="formData.submit_date" 
            placeholder="请选择提交日期"
            format="YYYY-MM-DD"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <!-- 金额相关 -->
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="contract_amount" label="签约金额">
          <a-input-number 
            v-model="formData.contract_amount" 
            placeholder="请输入签约金额" 
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="buyout_amount" label="买断金额">
          <a-input-number 
            v-model="formData.buyout_amount" 
            placeholder="请输入买断金额" 
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>
    
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="deposit" label="保证金">
          <a-input-number 
            v-model="formData.deposit" 
            placeholder="请输入保证金" 
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="releaseAmount" label="释放金额">
          <a-input-number 
            v-model="formData.release_amount" 
            placeholder="请输入释放金额" 
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
            @change="calculateAutoFields"
          />
        </a-form-item>
      </a-col>
    </a-row>
    
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="debt_settlement_amount" label="平债金额">
          <a-input-number 
            v-model="formData.debt_settlement_amount" 
            placeholder="请输入平债金额" 
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="commission_fee" label="提成费用">
          <a-input-number 
            v-model="formData.commission_fee" 
            placeholder="请输入提成费用" 
            mode="button"
            :min="0"
            :precision="2"
            style="width: 100%"
          />
        </a-form-item>
      </a-col>
    </a-row>
    
    <!-- 自动计算字段 -->
    <a-row :gutter="16">
      <a-col :span="12">
        <a-form-item field="contractRate" label="合同点位">
          <a-select v-model="formData.contract_rate" placeholder="请选择合同点位" @change="calculateAutoFields">
            <a-option value="10%">10%</a-option>
            <a-option value="15%">15%</a-option>
            <a-option value="20%">20%</a-option>
            <a-option value="25%">25%</a-option>
          </a-select>
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="receivableAmount" label="应收金额">
          <a-input-number 
            v-model="formData.receivable_amount" 
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
        <a-form-item field="rebateRate" label="返点点位">
          <a-select v-model="formData.rebate_rate" placeholder="请选择返点点位" @change="calculateAutoFields">
            <a-option value="10%">10%</a-option>
            <a-option value="15%">15%</a-option>
            <a-option value="20%">20%</a-option>
            <a-option value="25%">25%</a-option>
          </a-select>
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item field="rebateAmount" label="返点金额">
          <a-input-number 
            v-model="formData.rebate_amount" 
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
        <a-form-item field="commissionRate" label="提成点位">
          <a-input v-model="formData.commission_rate" placeholder="请输入提成点位" />
        </a-form-item>
      </a-col>

    </a-row>
    
    <!-- 其他信息 -->
    <a-form-item field="remark" label="备注">
      <a-textarea v-model="formData.remark" placeholder="请输入备注" />
    </a-form-item>
    
    <!-- 操作按钮 -->
    <a-form-item>
      <a-space size="medium" style="float: right;">
        <a-button @click="$emit('cancel')">取消</a-button>
        <a-button type="primary" @click="handleSubmit">确定</a-button>
      </a-space>
    </a-form-item>
  </a-form>
</template>

<script setup lang="ts">
import { reactive, watch, onMounted } from 'vue';
import type { FinanceApplication, Option } from '@/api/finance';

const props = withDefaults(defineProps<{
  initialData?: Partial<FinanceApplication>;
  isEdit?: boolean;
  cityOptions?: Option[];
  channelOptions?: Option[];
  userOptions?: Option[];
}>(), {
  isEdit: false,
  cityOptions: () => [
    { label: '厦门', value: '厦门' },
    { label: '杭州', value: '杭州' },
    { label: '武汉', value: '武汉' },
  ],
  channelOptions: () => [],
  userOptions: () => []
});

const formData = reactive<FinanceApplication>({
  id: undefined,
  application_number: '',
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
  housing_fund_base: 0,
  salary: 0,
  operation_date: '',
  status: 'pending',
  submit_date: '',
  approver: '',
  approval_date: '',
  remark: '',
  ...props.initialData
} as FinanceApplication);

const emit = defineEmits<{
  (e: 'cancel'): void;
  (e: 'save', data: FinanceApplication): void;
}>();

// 自动计算逻辑
const calculateAutoFields = () => {
  const release_amount = formData.release_amount || 0;
  
  // 返点金额 = 释放金额 × 返点点位
  if (formData.rebate_rate) {
    const rebate_rate = parseFloat(formData.rebate_rate.replace('%', '')) / 100;
    formData.rebate_amount = Number((release_amount * rebate_rate).toFixed(2));
  }
  
  // 应收金额 = 释放金额 × 合同点位
  if (formData.contract_rate) {
    const contract_rate = parseFloat(formData.contract_rate.replace('%', '')) / 100;
    formData.receivable_amount = Number((release_amount * contract_rate).toFixed(2));
  }
};

// 监听关键字段变化
watch(() => formData.release_amount, calculateAutoFields);
watch(() => formData.rebate_rate, calculateAutoFields);
watch(() => formData.contract_rate, calculateAutoFields);

// 初始计算
onMounted(() => {
  calculateAutoFields();
});

const handleSubmit = () => {
  emit('save', { ...formData });
};
</script>