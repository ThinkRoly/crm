<template>
  <div class="container">
    <Breadcrumb :items="['menu.system', 'menu.system.audit']" />
    <a-card class="general-card" :style="{ paddingTop: '30px' }">
      <a-row>
        <a-col :flex="1">
          <a-form
            :model="formModel"
            :label-col-props="{ span: 7 }"
            :wrapper-col-props="{ span: 17 }"
            label-align="left"
          >
            <a-row :gutter="16">
              <a-col :span="6">
                <a-form-item field="user_name" label="用户名称">
                  <a-input v-model="formModel.user_name" placeholder="请输入" />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="ip" label="IP">
                  <a-input v-model="formModel.ip" placeholder="请输入" />
                </a-form-item>
              </a-col>
              <a-col :span="10">
                <a-form-item field="operator_time" label="操作时间">
                  <a-range-picker
                    v-model="formModel.operator_time"
                    style="width: 100%"
                    show-time
                    :time-picker-props="{
                      defaultValue: ['00:00:00', '00:00:00'],
                    }"
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
            <a-button type="primary" @click="search">
              <template #icon><icon-search /></template>
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
        @page-change="onPageChange"
      >
        <template #columns>
          <a-table-column title="用户" data-index="user_name" />
          <a-table-column title="操作时间" data-index="create_time" />
          <a-table-column title="操作内容" data-index="operator" />
          <a-table-column title="IP" data-index="ip" />
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script lang="ts" setup>
  import { reactive, ref } from 'vue';
  import useLoading from '@/hooks/loading';
  import {
    AuditInfo,
    AuditListParams,
    queryAuditList,
  } from '@/api/system';

  const createFormModel = () => ({
    user_name: '',
    ip: '',
    operator_time: [] as string[],
  });

  const { loading, setLoading } = useLoading(true);
  const renderData = ref<AuditInfo[]>([]);
  const formModel = reactive(createFormModel());
  const basePagination = { current: 1, pageSize: 20 };
  const pagination = reactive({ ...basePagination, total: 0 });

  const fetchData = async (
    params: AuditListParams = { ...basePagination }
  ) => {
    setLoading(true);
    try {
      const { data } = await queryAuditList(params);
      renderData.value = data.list;
      pagination.current = params.current;
      pagination.total = data.total;
    } finally {
      setLoading(false);
    }
  };

  const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current, ...formModel });
  };

  const search = () => {
    fetchData({ ...basePagination, ...formModel });
  };

  fetchData();
</script>