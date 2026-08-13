<template>
  <div class="container">
    <Breadcrumb :items="['menu.system', 'menu.system.role']" />

    <a-card class="general-card" style="padding-top:30px">
      <a-row>
        <a-col :flex="1">
          <a-form :model="formModel" :label-col-props="{ span: 7 }" :wrapper-col-props="{ span: 17 }" label-align="left" >
            <a-row :gutter="16">
              <a-col :span="8">
                <a-form-item field="name" label="角色名称"> <a-input v-model="formModel.name" placeholder="请输入" /> </a-form-item>
              </a-col>
              <a-col :span="8">
                <a-form-item field="status" label="角色状态"> <a-select v-model="formModel.status" :options="contentTypeOptions" placeholder="请选择" allow-clear /> </a-form-item>
              </a-col>
            </a-row>
          </a-form>
        </a-col>
        <a-divider style="height: 38px" direction="vertical" />
      </a-row>
      <a-divider style="margin-top: 0" />
      <a-row style="margin-bottom: 16px">
        <a-col :span="16">
          <a-space>
            <a-button type="primary" @click="search"> <template #icon> <icon-search /> </template> 查询 </a-button>
            <a-button v-permission="['Roleedit']" type="primary" @click="$router.push({ name: 'Roleedit' })" status="success">
              <template #icon> <icon-plus /> </template> 新增
            </a-button>
          </a-space>
        </a-col>
      </a-row>
      <a-table row-key="id" :loading="loading" :pagination="pagination" :data="renderData" :bordered="false" label-align="center" @page-change="onPageChange" >
        <template #columns>
          <a-table-column title="角色id" data-index="id" />
          <a-table-column title="角色姓名" data-index="name" />
          <a-table-column title="当前状态">
            <template #cell="{ record }">
              <span v-if="record.status == 0" class="circle"></span>
              <span v-else class="circle pass"></span>
              {{ record.status == 0 ? '无效' : '有效' }}
            </template>
          </a-table-column>
          <a-table-column title="创建时间" data-index="create_time" />
          <a-table-column title="操作" data-index="operations">
            <template #cell="{ record }">
              <a-button v-permission="['Rolepreview']" type="text" size="small" @click=" $router.push({ name: 'Rolepreview', params: { id: record.id }, }) "> <icon-eye /> 查看</a-button>
              <a-button v-permission="['Roleedit']" type="text" size="small" @click=" $router.push({ name: 'Roleedit', params: { id: record.id } }) "> <icon-edit /> 编辑 </a-button>
              <a-popconfirm v-if="record.status == 1" content="确认执行该操作么?" ok-text="确认" cancel-text="取消" @ok="lock(record, '0')"> 
                <a-button type="text" size="small" v-permission="['Rolelock']"> <icon-lock /> 设为无效</a-button>
              </a-popconfirm>
              <a-popconfirm v-if="record.status == 0" content="确认执行该操作么?" ok-text="确认" cancel-text="取消" @ok="lock(record, '1')">
                <a-button type="text" size="small" v-permission="['Rolelock']"> <icon-unlock /> 设为有效</a-button>
              </a-popconfirm>
              <a-popconfirm :content="'确认删除'+record.name+'吗,删除后无法修复?'" ok-text="确认" cancel-text="取消" @ok="delRole(record.id)"
                 v-if="
                  (userStore.$state.buttonPermission.includes('Roledelete'))  
                "
              > 
                <a-button type="text" size="small"> <icon-delete /> 删除</a-button>
              </a-popconfirm>
            </template>
          </a-table-column>
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script lang="ts" setup>
  import { computed, ref, reactive } from 'vue';
  import { useI18n } from 'vue-i18n';
  import useLoading from '@/hooks/loading';
  import { queryRoleList, RoleInfo, RoleListParams, lockRole, deleteRole } from '@/api/system';
  import { Pagination } from '@/types/global';
  import { useAppStore } from '@/store';

  const userStore = useAppStore();
  const { loading, setLoading } = useLoading(true);
  const { t } = useI18n();
  const renderData = ref<RoleInfo[]>([]);
  const formModel = ref({'name':'', 'status':''});
  const basePagination: Pagination = { current: 1, pageSize: 20, };
  const pagination = reactive({ ...basePagination,});
  const contentTypeOptions = ref([ { label: '有效', value: '1', }, { label: '无效', value: 0, }]);
  const fetchData = async (
    params: RoleListParams = { current: 1, pageSize: 20 }
  ) => {
    setLoading(true);
    try {
      const { data } = await queryRoleList(params);
      renderData.value = data.list;
      pagination.current = params.current;
      pagination.total = data.total;
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };

  const search = () => {
    fetchData({
      ...basePagination,
      ...formModel.value,
    } as unknown as RoleListParams);
  };
  const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current });
  };

  fetchData();

  const lock = (record: RoleInfo, status: string) => {
    try {
      lockRole(record.id, status);
      record.status = status;
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };

  const delRole = async (id: string) => {
    try {
      await deleteRole(id);
      search();
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
</style>
