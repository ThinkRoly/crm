<template>
  <div class="container">
    <Breadcrumb :items="['menu.system', 'menu.system.team']" />

    <a-card class="general-card" style="padding-top:30px">
      <a-row>
        <a-col :flex="1">
          <a-form :model="formModel" :label-col-props="{ span: 7 }" :wrapper-col-props="{ span: 17 }" label-align="left" >
            <a-row :gutter="16">
              <a-col :span="8">
                <a-form-item field="name" label="团队名称"> <a-input v-model="formModel.name" placeholder="请输入" /> </a-form-item>
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
            <a-button v-permission="['Teamedit']" type="primary" @click="$router.push({ name: 'Teamedit' })" status="success">
              <template #icon> <icon-plus /> </template> 新增
            </a-button>
          </a-space>
        </a-col>
      </a-row>
      <a-table row-key="id" :loading="loading" :pagination="false" :data="renderData" :bordered="false" label-align="center" @page-change="onPageChange" :default-expand-all-rows="true" v-if="renderData.length">
        <template #columns>
          <a-table-column title="团队名称" data-index="name" />
          <a-table-column title="团队主管" data-index="manager" />
          <a-table-column title="团队人数" data-index="usercount" />
          <a-table-column title="创建时间" data-index="create_time" />
          <a-table-column title="操作" data-index="operations">
            <template #cell="{ record }">
              <a-button v-permission="['Teampreview']" type="text" size="small" @click=" $router.push({ name: 'Teampreview', params: { id: record.id }, }) "> <icon-eye /> 查看</a-button>
              <a-button v-permission="['Teamedit']" type="text" size="small" @click=" $router.push({ name: 'Teamedit', params: { id: record.id } }) "> <icon-edit /> 编辑 </a-button>
              <a-popconfirm content="确认删除该团队么？" ok-text="确认" cancel-text="取消" @ok="del(record.id)" v-if="userStore.$state.buttonPermission.includes('Teamdel')"> 
                <a-button type="text" size="small"> <icon-delete /> 删除 </a-button>
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
  import { useAppStore } from '@/store';
  import useLoading from '@/hooks/loading';
  import { queryTeamList, TeamInfo, TeamListParams, delTeam} from '@/api/system';
  import { Pagination } from '@/types/global';

  const { loading, setLoading } = useLoading(true);
  const renderData = ref<TeamInfo[]>([]);
  const formModel = ref({'name':''});
  const basePagination: Pagination = { current: 1, pageSize: 20, };
  const pagination = reactive({ ...basePagination,});
  const userStore = useAppStore();
  const expandedKeys = ref(["7"])
  const fetchData = async (
    params: TeamListParams = { current: 1, pageSize: 20 }
  ) => {
    setLoading(true);
    try {
      const { data } = await queryTeamList(params);
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
    } as unknown as TeamListParams);
  };
  const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current });
  };


  const del = (id: string) => {
    try {
      delTeam(id);
      search();
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };

  fetchData();
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
</style>
