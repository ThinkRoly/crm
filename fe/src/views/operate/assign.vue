<template>
  <div class="container">
    <Breadcrumb :items="['menu.operate', 'menu.operate.assign']" />

    <a-card class="general-card" style="padding-top:30px">
      <a-row>
        <a-col :flex="1">
          <a-form :model="formModel" :label-col-props="{ span: 7 }" :wrapper-col-props="{ span: 17 }" label-align="left" >
            <a-row :gutter="16">
              <a-col :span="8">
                <a-form-item field="name" label="用户名称"> <a-input v-model="formModel.name" placeholder="请输入" /> </a-form-item>
              </a-col>
              <a-col :span="8">
                <a-form-item field="status" label="用户团队"> <a-tree-select v-model="formModel.team_id" :data="contentTypeOptions" placeholder="请选择" allow-clear /> </a-form-item>
              </a-col>
            </a-row>
          </a-form>
        </a-col>
      </a-row>
      <a-divider style="margin-top: 0" />
      <a-row style="margin-bottom: 16px">
        <a-col :span="16">
          <a-space>
            <a-space  :size="18">
              <a-button type="primary" @click="search"> <template #icon> <icon-search /> </template> 查询 </a-button>
              <span>系统当日可接收新数据数量<font color="red">{{newlimit}}</font>条, 已接收<font color="red">{{newnow2}}</font>条,剩余可接收数据<font color="red">{{newlimit-newnow}}</font>条;</span>
              <span>系统当日可接收再分配数据数量<font color="red">{{innerlimit}}</font>条, 已接收<font color="red">{{innernow}}</font>条,剩余可接收数据<font color="red">{{innerlimit-innernow}}</font>条;</span>
            </a-space>
          </a-space>
        </a-col>
      </a-row>
      <a-table row-key="id" :loading="loading" :pagination="pagination" :data="renderData" :bordered="false" label-align="center" @page-change="onPageChange" >
        <template #columns>
          <a-table-column title="用户id" data-index="id" :width="80"/>
          <a-table-column title="用户姓名" data-index="name" :width="100" />
          <a-table-column title="用户团队" data-index="team" :width="120"/>
          <a-table-column title="在线状态" data-index="team" :width="100">
          <template #cell="{ record }">
              <span v-if="record.online == 0" class="circle"></span>
              <span v-else class="circle pass"></span>
              {{ record.online== 0 ? '离线' : '在线' }}
          </template>
          </a-table-column>
          <a-table-column title="名下总数据" data-index="nownow" />
          <a-table-column title="公共池领取上限" data-index="public" />
          <a-table-column title="再分配上限" data-index="inner" />
          <a-table-column title="今日再分配" data-index="again" />
          <a-table-column title="新数据上限" data-index="new" />
          <a-table-column title="今日新数据" data-index="now" />
          <a-table-column title="操作" data-index="operations" :width="400">
            <template #cell="{ record }">
              <a-button v-permission="['Assignpreview']" type="text" size="small" @click="openModal(record, true)"> <icon-eye /> 查看</a-button>
              <a-button v-permission="['Assignedit']" type="text" size="small" @click="openModal(record, false)"> <icon-edit /> 数据权限 </a-button>
              <a-button v-permission="['Assignlog']" type="text" size="small" @click="id=record.id;logModal=true"> <icon-settings /> 操作日志 </a-button>
              <a-popconfirm v-if="record.online == 0" content="确认执行上线操作么?" ok-text="确认" cancel-text="取消" @ok="onoffline(record, '1')">
                <span title="上线 ">
                <a-button type="text" size="small"> 上线 </a-button>
                </span>
              </a-popconfirm>
              <a-popconfirm v-if="record.online == 1" content="确认执行下线操作么?" ok-text="确认" cancel-text="取消" @ok="onoffline(record, '0')">
                <span title="下线">
                <a-button type="text" size="small">  下线 </a-button>
                </span>
              </a-popconfirm>
            </template>
          </a-table-column>
        </template>
      </a-table>
    </a-card>
  <a-modal
      v-model:visible="assignModal"
      title="数据权限"
      :footer="false"
    >
      <Edit
        :id="id"
        :preview="preview"
        :config="config"
        :closeModal="()=>{assignModal=false;search();}"
        ref="edit"
      />
    </a-modal>
    <a-modal
      v-model:visible="logModal"
      title="操作日志"
      :footer="false"
      :width="700"
      @close="()=>{id=0}"
    >
      <Log
        uri="/api/operate/assign/log"
        :id="id"
      />
    </a-modal>
  </div>
</template>

<script lang="ts" setup>
  import { computed, ref, reactive } from 'vue';
  import { useI18n } from 'vue-i18n';
  import useLoading from '@/hooks/loading';
  import { Message } from '@arco-design/web-vue';
  import {
    queryUserList,
    UserInfo,
    UserListParams,
    lockUser,
    resetUserPwd,
    ooUser
  } from '@/api/system';
  import { Pagination } from '@/types/global';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import Edit from './components/edit.vue';
  import Log from '../../components/log/log.vue';

  const generateFormModel = () => {
    return {
      mobile: '',
      name: '',
      status: 1,
      team_id: ''
    };
  };

  const assignModal = ref(false);
  const logModal = ref(false);
  const id = ref(0);
  const newlimit = ref(0);
  const innerlimit = ref(0);
  const newnow = ref(0);
  const newnow2 = ref(0);
  const innernow = ref(0);
  const preview = ref(true);
  const config = ref({});
  const { loading, setLoading } = useLoading(true);
  const { t } = useI18n();
  const renderData = ref<UserInfo[]>([]);
  const formModel = ref(generateFormModel());
  const basePagination: Pagination = { current: 1, pageSize: 20, };
  const pagination = reactive({ ...basePagination,});
  const contentTypeOptions = ref();
  const fetchData = async (
    params: UserListParams = { current: 1, pageSize: 20 }
  ) => {
    setLoading(true);
    try {
      const { data } = await queryUserList(params);
      renderData.value = data.list;
      contentTypeOptions.value = data.allTeamListSelect
      pagination.current = params.current;
      pagination.total = data.total;
      newlimit.value = data.newlimit;
      newnow.value = data.newnow;
      newnow2.value = data.newnow2;
      innerlimit.value = data.innerlimit;
      innernow.value = data.innernow;
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };
  const openModal = (record: any, preview1: any) => {
    assignModal.value=true;
    preview.value=preview1
    id.value =record.id
    try {
      const configTmp = JSON.parse(record.assign_rights)
      const returnData = {'types':[] as any[], 'publicLimit':'', 'newLimit':'', 'innerLimit':''}
      const types = [] as any[];
      Object.keys(configTmp).forEach(type => {
        types.push(type)
        if (type === 'public') {
          returnData.publicLimit = configTmp[type].toString();
        } else if (type === 'new') {
          returnData.newLimit = configTmp[type].toString();
        } else if (type === 'inner') {
          returnData.innerLimit = configTmp[type].toString();
        }
      });
      returnData.types = types;
      config.value = returnData;
    } catch (err) {
        config.value = {'types':[]};
    } finally {
      // 
    }
  }

  const search = () => {
    fetchData({
      ...basePagination,
      ...formModel.value,
    } as unknown as UserListParams);
  };
  const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current });
  };

  
  const onoffline = (record: UserInfo, status: string) => {
    try {
      ooUser(record.id, status);
      record.online = status;
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
