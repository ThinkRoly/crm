<template>
  <div class="container">
    <Breadcrumb :items="['menu.system', 'menu.system.product']" />

    <a-card class="general-card" style="padding-top:30px">
      <a-row>
        <a-col :flex="1">
          <a-form :model="formModel" :label-col-props="{ span: 7 }" :wrapper-col-props="{ span: 17 }" label-align="left" >
            <a-row :gutter="16">
              <a-col :span="8">
                <a-form-item field="name" label="产品名称"> <a-input v-model="formModel.name" placeholder="请输入" /> </a-form-item>
              </a-col>
            </a-row>
          </a-form>
        </a-col>
      </a-row>
      <a-divider style="margin-top: 0" />
      <a-row style="margin-bottom: 16px">
        <a-col :span="16">
          <a-space>
            <a-button type="primary" @click="search"> <template #icon> <icon-search /> </template> 查询 </a-button>
            <a-button v-permission="['ProductEdit']" type="primary" @click="$router.push({ name: 'ProductEdit' })" status="success">
              <template #icon> <icon-plus /> </template> 新增
            </a-button>
          </a-space>
        </a-col>
      </a-row>
      <a-table row-key="id" :loading="loading" :pagination="pagination" :data="renderData" :bordered="false" label-align="center" @page-change="onPageChange" >
        <template #columns>
          <a-table-column title="ID" data-index="id" />
          <a-table-column title="产品名称" data-index="name" />
          <a-table-column title="机构" data-index="bank" />
          <a-table-column title="额度" data-index="amount" />
          <a-table-column title="创建时间" data-index="create_time" />
          <a-table-column title="操作" data-index="operations">
            <template #cell="{ record }">
              <a-button v-permission="['ProductView']" type="text" size="small" @click=" $router.push({ name: 'ProductView', params: { id: record.id }, }) "> <icon-eye /> 查看</a-button>
              <a-button v-permission="['ProductEdit']" type="text" size="small" @click=" $router.push({ name: 'ProductEdit', params: { id: record.id } }) "> <icon-edit /> 编辑 </a-button>
            </template>
          </a-table-column>
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script lang="ts" setup>
  import { computed, ref, reactive } from 'vue';
  import useLoading from '@/hooks/loading';
  import { queryProductList } from '@/api/system';
  import { Pagination } from '@/types/global';

  const { loading, setLoading } = useLoading(true);
  const renderData = ref([]);
  const formModel = ref({'name':''});
  const basePagination: Pagination = { current: 1, pageSize: 20, };
  const pagination = reactive({ ...basePagination,});
  const fetchData = async (
    params: any
  ) => {
    setLoading(true);
    try {
      const { data }= await queryProductList(params);
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
    }  );
  };
  const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current });
  };

  search();
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
</style>
