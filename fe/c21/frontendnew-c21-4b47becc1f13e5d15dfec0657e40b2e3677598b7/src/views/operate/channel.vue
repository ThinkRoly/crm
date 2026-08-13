<template>
    <div class="container">
        <Breadcrumb :items="['menu.operate', 'menu.operate.channel']" />
        <a-form ref="formRef" :model="a" label-align="right" auto-label-width>
            <a-space direction="vertical" :size="16">
                <a-card class="general-card">
                    <template #title> 渠道列表 </template>
                    <a-row>
                        <a-col :flex="1">
                        <a-form :model="formModel" :label-col-props="{ span: 7 }" :wrapper-col-props="{ span: 17 }" label-align="left" >
                            <a-row :gutter="16">
                            <a-col :span="8">
                                <a-form-item field="name" label="渠道名称"> <a-input v-model="formModel.name" placeholder="请输入" /> </a-form-item>
                            </a-col>
                            <a-col :span="8">
                                <a-form-item field="status" label="渠道状态"> <a-select v-model="formModel.status" :options="contentTypeOptions" placeholder="请选择" allow-clear /> </a-form-item>
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
                            <a-button type="primary" @click="fetchData"> <template #icon> <icon-search /> </template> 查询 </a-button>
                            <a-button v-permission="['ChannelEdit']" type="primary" @click="$router.push({ name: 'ChannelEdit' })" status="success">
                            <template #icon> <icon-plus /> </template> 新增
                            </a-button>
                        </a-space>
                        </a-col>
                    </a-row>
                    <a-table row-key="id" :data="tableData" :bordered="false" label-align="center" >
                      <template #columns>
                        <a-table-column title="ID" data-index="app_id" />
                        <a-table-column title="名称" data-index="name" />
                        <a-table-column title="渠道展示名称" data-index="show_name" />
                        <a-table-column title="渠道成本(元)" data-index="cost" />
                        <a-table-column title="接入方式" data-index="type" />
                        <a-table-column title="渠道状态">
                          <template #cell="{ record }">
                            <span v-if="record.status == 0" class="circle"></span>
                            <span v-else class="circle pass"></span>
                            {{ record.status == 0 ? '暂停使用' : '使用中' }}
                          </template>
                        </a-table-column>
                        <a-table-column title="操作" data-index="operations">
                          <template #cell="{ record }">
                            <a-button v-permission="['ChannelView']" type="text" size="small" @click=" $router.push({ name: 'ChannelView', params: { id: record.id }, }) "> <icon-eye /> 查看</a-button>
                            <a-button v-permission="['ChannelEdit']" type="text" size="small" @click=" $router.push({ name: 'ChannelEdit', params: { id: record.id } }) "> <icon-edit /> 编辑 </a-button>
                            <a-popconfirm v-if="record.status == 1" content="确认执行该操作么?" ok-text="确认" cancel-text="取消" @ok="lock(record, '0')"> 
                               <a-button type="text" size="small" v-permission="['ChannelStatus']"> <icon-lock /> 立即停用</a-button>
                            </a-popconfirm>
                            <a-popconfirm v-if="record.status == 0" content="确认执行该操作么?" ok-text="确认" cancel-text="取消" @ok="lock(record, '1')">
                                <a-button type="text" size="small" v-permission="['ChannelStatus']"> <icon-unlock /> 恢复使用</a-button>
                            </a-popconfirm>
                          </template>
                        </a-table-column>
                      </template>
                    </a-table>
                </a-card>
            </a-space>
        </a-form>
    </div>
</template>

<script lang="ts" setup>
  import { ref } from 'vue';
  import { getChannelConfig, setChannelStatus } from '@/api/channel';
   
  // const host = ref((window as Window).location.host);
  const a = ref();
  const tableData = ref([{'id':'', 'type':'', 'config':'', 'token':'', 'name':'', remark:''}]);
  const formModel = ref({'name':'', 'status':''});
  const contentTypeOptions = ref([ { label: '有效', value: '1', }, { label: '无效', value: 0, }]);

    const fetchData = async () => {
      try {
        const { data } = await getChannelConfig(formModel.value);
        tableData.value = data.list;
      } catch (err) {
        //
      }
    };
    fetchData();


  const lock = (record: any, status: string) => {
    try {
      setChannelStatus(record.id, status);
      record.status = status;
    } catch (err) {
      //
    } finally {
      //
    }
  };
</script>
<style scoped lang="less">
.container {
    padding: 0 20px 20px 20px;
}
</style>