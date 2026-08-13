<template>
  <div class="container">
    <Breadcrumb :items="['menu.customer', 'menu.approve', 'menu.approve.other']" />
    <a-card class="general-card" style="padding-top: 30px">
        <a-row>
        <a-col :flex="1">
          <a-form :model="formModel" :label-col-props="{ span: 7 }" :wrapper-col-props="{ span: 17 }" label-align="left" >
            <a-row :gutter="16">
              <a-col :span="8">
                <a-form-item field="type" label="类型"> 
                    <a-select v-model="formModel.type" :options="typeOptions" placeholder="请选择" allow-clear /> 
                </a-form-item>
              </a-col>
              <a-col :span="8">
                <a-form-item field="status" label="审批状态"> 
                    <a-select v-model="formModel.status" :options="statusOptions" placeholder="请选择" allow-clear /> 
                </a-form-item>
              </a-col>
              <a-col :span="8">
                <a-button type="primary" @click="onPageChange(1)"> <template #icon> <icon-search /> </template> 查询 </a-button>
              </a-col>
            </a-row>
          </a-form>
        </a-col>
        <a-divider style="height: 38px" direction="vertical" />
      </a-row>
      <a-divider style="margin-top: 0" />
        <a-table row-key="id" :loading="loading"
            :pagination="pagination" :data="renderData" :bordered="false" label-align="center" @page-change="onPageChange">
            <template #columns>
                <a-table-column title="ID" data-index="id" :width="80" />
                <a-table-column title="流程名称" data-index="name" />
                <a-table-column title="发起人" data-index="user_name" />
                <a-table-column title="当前状态" data-index="status_name" />
                <a-table-column title="发起时间" data-index="create_time"  />
                <a-table-column title="操作" data-index="oper_user" > 
                    <template #cell="{ record }">
                        <a-button v-permission="['Approveview']" type="text" size="small"
                          @click="view(record.id)">
                          <icon-eye /> 详情
                        </a-button>
                    </template>
                </a-table-column>
            </template>
        </a-table>
  <a-drawer :width="340" :visible="visible" @cancel="visible=false" :footer="selectRecord.can_approve == 1"  unmountOnClose>
    <template #title>
        {{selectRecord.title}}
    </template>
    <template #footer>
        <a-space>
            <a-button size="mini" @click="pass(-1)">
                拒绝
            </a-button>
            <a-button type="primary" :loading="loading" size="mini" @click="pass(1)">
                通过
            </a-button>
        </a-space>
    </template>
    <div>
        <a-descriptions
          size='mini'
          title='详情'
          :column="1"
          :data="selectRecord.detail"
        />
        <Divider />
        <a-descriptions
          size='mini'
          title='进度'
          :column="1"
          :data="selectRecord.approve"
        />
    </div>
  </a-drawer>
</a-card>
  </div>
</template> 

<script lang="ts" setup>
import { ref, reactive, computed } from 'vue';
import useLoading from '@/hooks/loading';
import { queryList, cancelApprove, getApprove, passApprove} from '@/api/approve';
import { Pagination } from '@/types/global';
import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
import {setNum} from '@/api/customernum';

const { loading, setLoading } = useLoading(true);
const renderData = ref([]);
const basePagination: Pagination = { current: 1, pageSize: 20, };
const pagination = reactive({ ...basePagination, });
const formModel = ref({'type':'', 'status':0, 'ltype':'2'})
const typeOptions = ref<SelectOptionData[]>([])
const statusOptions = ref<SelectOptionData[]>([])
const selectRecord = ref({'detail':[], 'approve':[], 'title':'', 'can_approve':0, 'id':0})
const visible = ref(false);


const fetchData = async (
    params: Pagination,
) => {
    setLoading(true);
    try {
        const { data } = await queryList({...params, ...formModel.value});
        renderData.value = data.list;
        pagination.current = params.current;
        pagination.total = data.total;
        typeOptions.value = data.types as SelectOptionData[]
        statusOptions.value = data.status as SelectOptionData[]
    } catch (err) {
        //
    } finally {
        setLoading(false);
    }
};

const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current });
};

fetchData( { current: 1, pageSize: 20 });

const cancel = async (id:number) => {
    try {
        await cancelApprove(id);
        setNum();
        fetchData(pagination);
    } catch (err) {
        //
    } finally {
        setLoading(false);
    }
}

const view = async (id: number) => {
    try {
        const data = await getApprove(id);
        selectRecord.value = data.data
        selectRecord.value.id = id
        visible.value = true;
    } catch (err) {
        //
    } finally {
        setLoading(false);
    }
}

const pass = async (status: number) => {
    try {
        const data = await passApprove(selectRecord.value.id, status);
        fetchData(pagination);
        visible.value = false;
        setNum();
    } catch (err) {
        //
    } finally {
        setLoading(false);
    }
}


</script>

<style scoped lang="less">
.container {
    padding: 0 20px 20px 20px;
}
</style>