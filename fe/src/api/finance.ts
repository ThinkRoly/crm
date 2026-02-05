import axios from 'axios';
import type { HttpResponse } from './interceptor';

// 进件管理相关接口
export interface FinanceApplication {
  id?: number;
  customer_name: string;
  city: string;
  channel: string;
  sign_date: string;
  salesperson: string;
  repayment_date: string;
  notarization: string;
  contract_amount: number;
  contract_rate: string;
  receivable_amount: number;
  buyout_amount: number;
  deposit: number;
  release_amount: number;
  rebate_rate: string;
  rebate_amount: number;
  commission_rate: string;
  commission_fee: number;
  risk_control_person: string;
  debt_settlement_amount: number;
  department: string;
  household: string;
  education: string;
  company_full_name: string;
  company_type: string;
  housing_fund_base: string;
  salary: string;
  operation_date: string;
  remark: string;
}

export interface FinanceApplicationQuery {
  page: number;
  pageSize: number;
  customer_name?: string;
  city?: string;
  channel?: string;
  sign_date?: string;
  salesperson?: string;
}

// 选项类型
export interface Option {
  label: string;
  value: string;
}

// 进件管理列表响应数据类型
export interface FinanceApplicationListResponse {
  list: FinanceApplication[];
  total: number;
  cityOptions: Option[];
  channelOptions: Option[];
  userOptions: Option[];
}

export function getFinanceApplicationList(params: FinanceApplicationQuery) {
  return axios.get<HttpResponse<FinanceApplicationListResponse>>('/api/finance/application/list', { params });
}

export function getFinanceApplication(id: number) {
  return axios.get<HttpResponse<FinanceApplication>>(`/api/finance/application/${id}`);
}

export function createFinanceApplication(data: FinanceApplication) {
  return axios.post<HttpResponse<FinanceApplication>>('/api/finance/application/edit', data);
}

export function updateFinanceApplication(id: number, data: FinanceApplication) {
  return axios.put<HttpResponse<FinanceApplication>>(`/api/finance/application/edit`, data);
}

export function deleteFinanceApplication(id: number) {
  return axios.post<HttpResponse<unknown>>(`/api/finance/application/delete`);
}

// 回款管理相关接口
export interface FinancePayment {
  id?: number;
  billNumber: string;
  customerName: string;
  amount: number;
  receivedAmount: number;
  receivedDate: string;
  status: string;
  remark: string;
}

export interface FinancePaymentQuery {
  page: number;
  pageSize: number;
  customerName?: string;
  status?: string;
}

export function getFinancePaymentList(params: FinancePaymentQuery) {
  return axios.get<HttpResponse<{list: FinancePayment[], total: number}>>('/api/finance/payment/list', { params });
}

export function getFinancePayment(id: number) {
  return axios.get<HttpResponse<FinancePayment>>(`/api/finance/payment/${id}`);
}

export function createFinancePayment(data: FinancePayment) {
  return axios.post<HttpResponse<FinancePayment>>('/api/finance/payment/edit', data);
}

export function updateFinancePayment(id: number, data: FinancePayment) {
  return axios.put<HttpResponse<FinancePayment>>(`/api/finance/payment/edit`, data);
}

export function deleteFinancePayment(id: number) {
  return axios.delete<HttpResponse<unknown>>(`/api/finance/payment/${id}`);
}

// 出款管理相关接口
export interface FinanceDisbursement {
    id?: number;
    application_id: number;
    customer_name: string;
    channel: string;
    city: string;
    application_number: string;
    amount: number;
    disbursement_date: string;
    account_name: string;
    bank_name: string;
    bank_account: string;
    purpose: string;
    status: 'pending' | 'success' | 'failed';
    operator: string;
    remark: string;
}

export interface FinanceDisbursementQuery {
  page: number;
  pageSize: number;
  customerName?: string;
  status?: string;
}

export function getFinanceDisbursementList(params: FinanceDisbursementQuery) {
  return axios.get<HttpResponse>('/api/finance/disbursement/list', { params });
}

export function getFinanceDisbursement(id: number) {
  return axios.get<HttpResponse>(`/api/finance/disbursement/${id}`);
}

export function createFinanceDisbursement(data: FinanceDisbursement) {
  return axios.post<HttpResponse>('/api/finance/disbursement', data);
}

export function updateFinanceDisbursement(
  id: number,
  data: FinanceDisbursement
) {
  return axios.put<HttpResponse>(`/api/finance/disbursement/${id}`, data);
}

export function deleteFinanceDisbursement(id: number) {
  return axios.delete<HttpResponse>(`/api/finance/disbursement/${id}`);
}

// 账单管理相关接口
export interface FinanceBill {
  id?: number;
  billNumber: string;
  customerName: string;
  amount: number;
  dueDate: string;
  status: string;
  paymentStatus: string;
  remark: string;
}

export interface FinanceBillQuery {
  page: number;
  pageSize: number;
  customerName?: string;
  status?: string;
}

export function getFinanceBillList(params: FinanceBillQuery) {
  return axios.get<HttpResponse>('/api/finance/bill/list', { params });
}

export function getFinanceBill(id: number) {
  return axios.get<HttpResponse>(`/api/finance/bill/${id}`);
}

export function createFinanceBill(data: FinanceBill) {
  return axios.post<HttpResponse>('/api/finance/bill', data);
}

export function updateFinanceBill(id: number, data: FinanceBill) {
  return axios.put<HttpResponse>(`/api/finance/bill/${id}`, data);
}

export function deleteFinanceBill(id: number) {
  return axios.delete<HttpResponse>(`/api/finance/bill/${id}`);
}

// 还款计划相关接口
export interface FinanceRepaymentPlan {
  id?: number;
  order_id: string;
  customer_name: string;
  sign_date: string;
  total_period: number;
  loan_amount: number;
  annual_rate: string;
  monthly_repayment_amount: number;
  repayment_method: string;
  paid_period: number;
  paid_amount: number;
  remaining_amount: number;
  overdue_days: number;
  period: number;
  due_date: string;
  due_amount: number;
  status: string;
}

export interface FinanceRepaymentPlanQuery {
  order_id?: string;
  page: number;
  pageSize: number;
}

export function getFinanceRepaymentPlan(params: FinanceRepaymentPlanQuery) {
  return axios.get<HttpResponse<{list: FinanceRepaymentPlan[], total: number}>>('/api/finance/repayment-plan/list', { params });
}

// 订单汇总相关接口
export interface FinanceOrder {
  id?: number;
  order_no: string;
  customer_name: string;
  loan_amount: number;
  period: number;
  total_period: number;
  paid_period: number;
  remaining_amount: number;
  paid_amount: number;
  next_repayment_date: string;
  status: string;
}

export interface FinanceOrderQuery {
  page: number;
  pageSize: number;
  order_no?: string;
  customer_name?: string;
}

export function getFinanceOrderList(params: FinanceOrderQuery) {
  return axios.get<HttpResponse<{list: FinanceOrder[], total: number}>>('/api/finance/order/list', { params });
}
