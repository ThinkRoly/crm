<template>
  <div class="container">
    <Breadcrumb :items="['menu.system', 'menu.system.user']" />

    <a-card class="general-card" style="padding-top:30px">
      <a-row>
        <a-col :flex="1">
          <a-form :model="formModel" :label-col-props="{ span: 7 }" :wrapper-col-props="{ span: 17 }" label-align="left" >
            <a-row :gutter="16">
              <a-col :span="6">
                <a-form-item field="name" label="用户名称"> <a-input v-model="formModel.name" placeholder="请输入" /> </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="mobile" label="手机号"> <a-input v-model="formModel.mobile" placeholder="请输入" /> </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="status" label="用户状态"> <a-select v-model="formModel.status" :options="contentTypeOptions" placeholder="请选择" allow-clear /> </a-form-item>
              </a-col>
              <a-col :span="6">
                <a-form-item field="status" label="在线状态"> <a-select v-model="formModel.online" :options="onlineOptions" placeholder="请选择" allow-clear /> </a-form-item>
              </a-col>
            </a-row>
          </a-form>
        </a-col>
      </a-row>
      <a-divider style="margin-top: 0" />
      <a-row style="margin-bottom: 16px">
        <a-col :span="16">
          <a-space>
            <a-space direction="vertical" :size="18">
              <a-button type="primary" @click="search"> <template #icon> <icon-search /> </template> 查询 </a-button>
            </a-space>
            <a-button v-permission="['Useredit']" type="primary" @click="$router.push({ name: 'Useredit' })" status="success">
              <template #icon> <icon-plus /> </template> 新增
            </a-button>
          </a-space>
        </a-col>
      </a-row>
      <a-table row-key="id" :loading="loading" :pagination="pagination" :data="renderData" :bordered="false" label-align="center" @page-change="onPageChange" >
        <template #columns>
          <a-table-column title="id" data-index="id" />
          <a-table-column title="用户姓名" data-index="name" />
          <a-table-column title="当前状态">
            <template #cell="{ record }">
              <span v-if="record.status == 0" class="circle"></span>
              <span v-else class="circle pass"></span>
              {{ record.status == 0 ? '无效' : '有效' }}
            </template>
          </a-table-column>
          <a-table-column title="在线状态">
            <template #cell="{ record }">
              <span v-if="record.online == 0" class="circle"></span>
              <span v-else class="circle pass"></span>
              {{ record.online== 0 ? '离线' : '在线' }}
            </template>
          </a-table-column>
          <a-table-column title="用户角色" data-index="roles" />
          <a-table-column title="用户团队" data-index="team" />
          <a-table-column title="操作" data-index="operations">
            <template #cell="{ record }">
              <span title="查看">
              <a-button v-permission="['Userpreview']" type="text" size="small" @click=" $router.push({ name: 'Userpreview', params: { id: record.id }, }) " > 查看 </a-button>
              </span>
              <span title="编辑">
              <a-button v-permission="['Useredit']" type="text" size="small" @click=" $router.push({ name: 'Useredit', params: { id: record.id } }) "> 编辑 </a-button>
              </span>
              <span title="设置权限">
              <a-button v-permission="['Userrole']" type="text" size="small" @click=" $router.push({ name: 'Userrole', params: { id: record.id } }) "> 设置权限 </a-button>
              </span>
              <a-popconfirm :content="'确认删除'+record.name+'吗,删除后无法修复?'" ok-text="确认" cancel-text="取消" @ok="delUser(record.id)"
                 v-if="
                  (userStore.$state.buttonPermission.includes('Userdelete'))  
                "
              > 
                <span title="删除">
                <a-button type="text" size="small"> 删除</a-button>
                </span>
              </a-popconfirm>
              <a-popconfirm :content="'确认重置'+record.name+'初始密码吗?'" ok-text="确认" cancel-text="取消" @ok="resetPwd(record)"> 
                <span title="重置密码">
                <a-button type="text" size="small"> 重置密码 </a-button>
                </span>
              </a-popconfirm>
              <a-popconfirm v-if="record.status == 1" content="确认执行该操作么?" ok-text="确认" cancel-text="取消" @ok="lock(record, '0')"> 
                <span title="冻结">
                <a-button type="text" size="small"> 冻结</a-button>
                </span>
              </a-popconfirm>
              <a-popconfirm v-if="record.status == 0" content="确认执行该操作么?" ok-text="确认" cancel-text="取消" @ok="lock(record, '1')">
                <span title="取消冻结">
                <a-button type="text" size="small"> 解冻 </a-button>
                </span>
              </a-popconfirm>
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
    deleteUser,
    ooUser
  } from '@/api/system';
  import { Pagination } from '@/types/global';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import { useAppStore } from '@/store';

  const userStore = useAppStore();
  const generateFormModel = () => {
    return {
      mobile: '',
      name: '',
      status: '',
      online : '',
    };
  };
  const { loading, setLoading } = useLoading(true);
  const { t } = useI18n();
  const renderData = ref<UserInfo[]>([]);
  const formModel = ref(generateFormModel());
  const basePagination: Pagination = { current: 1, pageSize: 20, };
  const pagination = reactive({ ...basePagination,});
  const contentTypeOptions = computed<SelectOptionData[]>(() => [ { label: '有效', value: '1', }, { label: '无效', value: 0, }, ]);
  const onlineOptions= computed<SelectOptionData[]>(() => [ { label: '在线', value: '1', }, { label: '离线', value: 0, }, ]);
  const fetchData = async (
    params: UserListParams = { current: 1, pageSize: 20 }
  ) => {
    setLoading(true);
    try {
      const { data } = await queryUserList(params);
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
    } as unknown as UserListParams);
  };
  const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current, ...formModel.value });
  };

  fetchData();

  const lock = (record: UserInfo, status: string) => {
    try {
      lockUser(record.id, status);
      record.status = status;
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };
  const delUser = async (id: string) => {
    try {
      await deleteUser(id);
      search();
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };



  const resetPwd = (record: UserInfo) => {
    try {
      resetUserPwd(record.id);
      Message.success({
        content: '重置成功', 
        duration: 5 * 1000,
      });
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
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
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
  .arco-btn-size-small{padding:0px 4px;}
</style>
