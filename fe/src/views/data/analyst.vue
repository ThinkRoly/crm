<template>
  <div class="container bigtable">
    <Breadcrumb :items="['menu.data', 'menu.data.work']" />

    <a-card class="general-card" style="padding-top: 30px">
      <a-row>
        <a-col :flex="1">
          <a-form
            :model="formModel"
            :label-col-props="{ span: 7 }"
            :wrapper-col-props="{ span: 17 }"
            label-align="left"
            :auto-label-width="true"
          >
            <a-row :gutter="16">
              <a-col :span="6">
                <a-form-item field="name" label="跟进顾问">
                  <a-select
                    v-model="formModel.user_id"
                    :options="userList"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="name" label="团队">
                  <a-tree-select
                    v-model="formModel.team_id"
                    :data="teamList"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="name" label="时间">
                  <a-range-picker
                    v-model="formModel.time"
                    style="width: 100%"
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
              <template #icon> <icon-search /> </template> 查询
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
      >
        <template #columns>
          <a-table-column title="用户" data-index="name" />
          <a-table-column title="团队" data-index="team" />
          <a-table-column title="新用户" data-index="new" />
          <a-table-column title="1星" data-index="star1" />
          <a-table-column title="2星" data-index="star2" />
          <a-table-column title="3星" data-index="star3" />
          <a-table-column title="4星" data-index="star4" />
          <a-table-column title="5星" data-index="star5" />
        </template>
      </a-table>
    </a-card>
  </div>
</template>

<script lang="ts" setup>
  import { ref, reactive } from 'vue';
  import useLoading from '@/hooks/loading';
  import { Pagination } from '@/types/global';
  import { queryWorkList, queryAnalystList } from '@/api/data';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import { useAppStore } from '@/store';

  const { loading, setLoading } = useLoading(true);
  const userStore = useAppStore();
  const renderData = ref([]);
  const formModel = ref({
    user_id: '',
    team_id: '',
    time: []
  });
  const basePagination: Pagination = { current: 1, pageSize: 1000 };
  const pagination = reactive({ ...basePagination });
  const teamList = ref<SelectOptionData[]>([]);
  const userList = ref<SelectOptionData[]>([]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const { data } = await queryAnalystList(formModel.value);
      renderData.value = data.list;
      teamList.value = data.allTeamListSelect;
      userList.value = data.followUserList;
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };
  const search = () => {
    fetchData();
  };

  search();
</script>

<style scoped lang="less">
  .container {
    padding: 0 20px 20px 20px;
  }
</style>

<style>
  .bigtable .arco-table .arco-table-cell {
    padding: 5px 10px;
  }

  .bigtable .arco-link,
  .container.bigtable .arco-table .arco-table-th,
  .bigtable .arco-table .arco-table-td {
    font-size: 12px;
  }

  .bigtable .arco-link {
    color: rgb(var(--gray-10));
  }
</style>
