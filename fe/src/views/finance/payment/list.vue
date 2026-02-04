<template>
  <div class="container">
    <Breadcrumb :items="['menu.finance', 'menu.finance.repayment']" />

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
                    <a-option value="厦门">厦门</a-option>
                    <a-option value="杭州">杭州</a-option>
                    <a-option value="武汉">武汉</a-option>
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
            <a-button type="primary" status="success" @click="handleAdd">
              <template #icon>
                <icon-plus />
              </template>
              新增回款
            </a-button>
            <a-popconfirm
              content="确认删除选中的回款记录吗？"
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
          <a-table-column title="通道点位" data-index="channel_point" />
          <a-table-column title="通道费用">
            <template #cell="{ record }"> ¥{{ record.channel_fee }} </template>
          </a-table-column>
          <a-table-column title="业务员" data-index="salesperson" />
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
                  content="确认删除该回款记录吗？"
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
          field="customer_name"
          label="客户姓名"
          :rules="[{ required: true, message: '请输入客户姓名' }]"
        >
          <a-input
            v-model="formData.customer_name"
            placeholder="请输入客户姓名"
          />
        </a-form-item>

        <a-form-item
          field="channel"
          label="对接渠道"
          :rules="[{ required: true, message: '请选择对接渠道' }]"
        >
          <a-select v-model="formData.channel" placeholder="请选择对接渠道">
            <a-option
              v-for="item in channelOptions"
              :key="item.value"
              :value="item.value"
              >{{ item.label }}</a-option
            >
          </a-select>
        </a-form-item>

        <a-form-item
          field="city"
          label="城市"
          :rules="[{ required: true, message: '请选择城市' }]"
        >
          <a-select v-model="formData.city" placeholder="请选择城市">
            <a-option value="厦门">厦门</a-option>
            <a-option value="杭州">杭州</a-option>
            <a-option value="武汉">武汉</a-option>
          </a-select>
        </a-form-item>

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

        <a-form-item field="channel_fee" label="通道费用">
          <a-input
            v-model="formData.channel_fee"
            disabled
            placeholder="自动计算"
          />
        </a-form-item>

        <a-form-item field="salesperson" label="业务员">
          <a-select v-model="formData.salesperson" placeholder="请选择业务员">
            <a-option
              v-for="item in salespersonOptions"
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
  import { ref, reactive, onMounted } from 'vue';
  import {
    getFinancePaymentList,
    createFinancePayment,
    updateFinancePayment,
    deleteFinancePayment,
    type FinancePayment,
    type FinancePaymentQuery,
  } from '@/api/finance';
  import { Message } from '@arco-design/web-vue';
  import Breadcrumb from '@/components/breadcrumb/index.vue';

  // 下拉选项数据
  const channelOptions = ref([
    { value: '渠道A', label: '渠道A' },
    { value: '渠道B', label: '渠道B' },
    { value: '渠道C', label: '渠道C' },
  ]);

  const salespersonOptions = ref([
    { value: '业务员A', label: '业务员A' },
    { value: '业务员B', label: '业务员B' },
    { value: '业务员C', label: '业务员C' },
  ]);

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
  const formData = ref<any>({
    id: undefined,
    customer_name: '',
    channel: '',
    city: '',
    sign_date: '',
    repayment_amount: 0,
    repayment_date: '',
    repayment_type: '',
    channel_point: '',
    channel_fee: 0,
    salesperson: '',
    remark: '',
  });

  // 计算通道费用
  const calculateChannelFee = () => {
    if (formData.value.repayment_amount && formData.value.channel_point) {
      // 通道费用 = 回款金额 * 通道点位
      formData.value.channel_fee = Number(
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
    searchForm.repayment_type = '';
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

  // 新增回款
  const handleAdd = () => {
    modalTitle.value = '新增回款';
    formData.value = {
      id: undefined,
      customer_name: '',
      channel: '',
      city: '',
      sign_date: '',
      repayment_amount: 0,
      repayment_date: '',
      repayment_type: '',
      channel_point: '',
      channel_fee: 0,
      salesperson: '',
      remark: '',
    };
    modalVisible.value = true;
  };

  // 编辑回款
  const handleEdit = (record: any) => {
    modalTitle.value = '编辑回款';
    formData.value = { ...record };
    modalVisible.value = true;
  };

  // 查看回款
  const handleView = (_record: any) => {
    // 这里可以打开查看弹窗或跳转到详情页
    Message.info('查看回款功能');
  };

  // 删除回款
  const handleDelete = async (id: number) => {
    try {
      const response = await deleteFinancePayment(id);
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
      Message.warning('请先选择要删除的回款记录');
      return;
    }

    try {
      // 这里调用批量删除API，如果后端没有提供批量删除接口，则逐个删除
      const deletePromises = selectedRows.value.map((id) =>
        deleteFinancePayment(id)
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
      calculateChannelFee();

      if (formData.value.id) {
        // 更新
        const response = await updateFinancePayment(
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
        const response = await createFinancePayment(formData.value);
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
