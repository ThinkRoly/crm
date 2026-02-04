<template>
  <div class="container">
    <Breadcrumb :items="['menu.system.notice']" />

    <a-card class="general-card" style="padding-top:30px">
      <a-row>
        <a-col :flex="1">
          <a-form :model="formModel" :label-col-props="{ span: 7 }" :wrapper-col-props="{ span: 17 }" label-align="left" >
            <a-row :gutter="16">
              <a-col :span="8">
                <a-form-item field="name" label="客户名称"> <a-input v-model="formModel.name" placeholder="请输入" /> </a-form-item>
              </a-col>
              <a-col :span="10">
                <a-form-item field="status" label="提醒时间">
                  <a-range-picker v-model="formModel.time" style="width: 100%" show-time />
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
            <a-button type="primary" @click="search"> <template #icon> <icon-search /> </template> 查询 </a-button>
          </a-space>
        </a-col>
      </a-row>
      <a-table row-key="id" :loading="loading" :pagination="pagination" :data="renderData" :bordered="false" label-align="center" @page-change="onPageChange" >
        <template #columns>
          <a-table-column title="id" data-index="id" />
          <a-table-column title="客户姓名" data-index="name" >
            <template #cell="{ record }">
              <a-link
                @click="follow(record.follow_user_id)"
              >
                {{ record.name }}
              </a-link>

            </template>
          </a-table-column>
          <a-table-column title="日程类型" data-index="type" />
          <a-table-column title="备注" data-index="remark" />
          <a-table-column title="提醒时间" data-index="time" />
          <a-table-column title="状态" data-index="is_read" >
            <template #cell="{record}">
              {{ record.is_read == 1 ? '已读' : '未读'}}
            </template>
          </a-table-column>
          <a-table-column title="创建时间" data-index="create_time" />
          <a-table-column title="操作" data-index="operations">
            <template #cell="{ record }">
              <a-button type="text" size="small" v-if="record.is_read==0 && record.can_read == 1" @click="readMessage(record.id)"> <icon-eye /> 已读</a-button>
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
  import { queryNoticeList, NoticeInfo, NoticeListParams, setMessageStatus} from '@/api/dashboard';
  import { Pagination } from '@/types/global';
  import { onBeforeRouteLeave, useRoute, useRouter } from 'vue-router';

  const route = useRoute();
  const router = useRouter();
  const customIds = ref();
  const { loading, setLoading } = useLoading(true);
  const renderData = ref<NoticeInfo[]>([]);
  const formModel = ref({'name':'', time: []});
  const basePagination: Pagination = { current: 1, pageSize: 20, };
  const pagination = reactive({ ...basePagination,});
  const follow = (id: any) => {
      const routeUrl = router.resolve({
        name: 'CustomerFollow',
        params: { id, allIds: customIds.value },
      });
      window.open(routeUrl.href, '_blank');
  };
  const fetchData = async (
    params: NoticeListParams = { current: 1, pageSize: 20 }
  ) => {
    setLoading(true);
    try {
      const { data } = await queryNoticeList(params);
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
      'time[]': formModel.value.time,
    } as unknown as NoticeListParams);
  };
  const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current });
  };

  async function readMessage(id: number) {
    await setMessageStatus({ 'ids' : [id] });
    search();
  }
  fetchData();
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
</style>
