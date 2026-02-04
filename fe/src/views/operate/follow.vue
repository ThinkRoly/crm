<template>
    <div class="container">
        <Breadcrumb :items="['menu.operate', 'menu.operate.follow']" />
        <a-form ref="formRef" :model="no" label-align="right" auto-label-width>
            <a-space direction="vertical" :size="16">
                <a-card class="general-card">
                    <template #title> 流转规则配置</template>
                    <a-alert title="说明">
                        1、数据分配的前提是，系统用户已开通客户分配管理权限且角色为销售顾问 <br/>
                        2、规则调整后，10分钟内生效 <br/>
                        3、开启状态表示规则执行中，关闭规则则所有逻辑不在运营 <br/>
                        注：为保证系统及数据稳定性，切勿频繁调整客户流转规则
                    </a-alert>
                    <br/>
                    <a-row class="grid-demo" style="background-color: var(--color-neutral-2); color: rgb(var(--gray-10)); font-weight: 700; line-height: 50px; height: 50px; border: 1px solid var(--color-neutral-3); padding: 0px 10px;">
                        <a-col :span="1">
                        <div>ID</div>
                        </a-col>
                        <a-col :span="4">
                        <div>规则类型</div>
                        </a-col>
                        <a-col :span="19">
                        <div>配置规则
                        </div>
                        </a-col>
                    </a-row>
                    <a-row class="grid-demo" v-for="(item, index) in tableData" :key="index" style="color: rgb(var(--gray-10));line-height: 50px; border-top: 0px; border: 1px solid var(--color-neutral-3); padding: 0px 10px;border-top: 0px;">
                        <a-col :span="1"> 
                            <a-switch type="round" v-model="item.status" :checked-value="1" :unchecked-value="0"/>
                        </a-col>
                        <a-col :span="4"> {{item.name}} </a-col>
                        <a-col :span="19">
                            <div v-if="item.id=='1'">
                                轮循平均分配/优先分配给当日分配数据最少的销售顾问
                            </div>
                            <div v-if="item.id=='2'">
                              新客户数据在客户经理名下
                               <a-input v-model="item.config.hour" :style="{width:'100px'}"></a-input> 
                              (小时) 未变更跟进状态/增加跟进备注，客户掉入公海
                            </div>
                            <div v-if="item.id=='13'">
                              再分配客户数据在客户经理名下
                               <a-input v-model="item.config.hour" :style="{width:'100px'}"></a-input> 
                              天未变更跟进状态/增加跟进备注，客户掉入公海
                            </div>
                            <div v-if="item.id=='16'">
                              已上门连续7天，已签约连续15天，银行已放款连续100天未跟进客户，掉入公海
                            </div>
                            <div v-if="item.id=='7' || item.id=='3' || item.id=='4' || item.id=='5' || item.id=='6'">
                                客户
                                <a-select v-model="item.config.type" @change="updateType(item, true)" :style="{width:'130px'}" :options="chooseoptions1"></a-select>
                                为
                                <a-select multiple v-model="item.config.values" :max-tag-count="2" :style="{width:'300px'}" :options="item.op1"></a-select>
                                ，且客户经理连续
                               <a-input v-model="item.config.day" :style="{width:'50px'}"></a-input> 
                                天未对客户进行跟进，客户流入公共池
                            </div>
                            <div v-if="item.id == '8'">
                                客户进入公海后
                                <a-select v-model="item.config.type" :style="{width:'160px'}">
                                    <a-option :value="1">全部客户经理</a-option>
                                    <a-option :value="2">原客户经理</a-option>
                                </a-select>
                                在
                                <a-input v-model="item.config.day" :style="{width:'50px'}"></a-input> 
                                （天）内不可以重新领取
                            </div>
                            <div v-if="item.id == '9'">
                                允许员工在公海领取客户的时间为
                                <a-time-picker
                                    type="time-range"
                                    v-model="item.config.times"
                                    style="width: 252px;" />
                            </div>
                            <div v-if="item.id == '10'">
                              每天
                                <a-time-picker
                                   style="width: 194px"
                                   v-model="item.config.time"
                                 />
                              点，将公共池里入库时间超过
                                <a-input v-model="item.config.day" :style="{width:'50px'}"></a-input> 
                              天的数据平均分配给客户经理
                            </div>
                            <div v-if="item.id == '11'">
                                最多可锁定客户数量: 
                                <a-input v-model="item.config.num" :style="{width:'50px'}"></a-input> 
                                , 最多能锁定时长:
                                <a-input v-model="item.config.day" :style="{width:'50px'}"></a-input> 
                                天
                            </div>
                            <div v-if="item.id == '12'">
                              销售顾问名下最多允许拥有客户数量
                                <a-input v-model="item.config.num" :style="{width:'100px'}"></a-input> 
                            </div>
                            <div v-if="item.id == '14'">
                              主管名下最多允许拥有客户数量
                              <a-input v-model="item.config.num" :style="{width:'100px'}"></a-input> 
                            </div>
                            <div v-if="item.id == '15'">
                              区长名下最多允许拥有客户数量
                              <a-input v-model="item.config.num" :style="{width:'100px'}"></a-input> 
                            </div>
                        </a-col>
                    </a-row>
                </a-card>
            </a-space>
        </a-form>
        <div class="actions">
            <a-space>
                <a-button type="primary" @click="submitHandler">
                    提交
                </a-button>
            </a-space>
        </div>
    </div>
</template>

<script lang="ts" setup>
  import { computed, ref, reactive, h } from 'vue';
  import { getAssignConfig, saveAssignConfigStatus, setAssignConfigStatus } from '@/api/assign';
    import { Message } from '@arco-design/web-vue';
  
  const no = ref({});
  const options  = ref([] as any);
  const staroptions  = ref([] as any);
  const chooseoptions1  = ref([ { label: '跟进状态', value: '1', }, { label: '星级', value: '2', }]);
  
  const tableData = ref([{'id':'', 'limit':'', 'status':'', 'name':'', 'op1' : [],  "config": {
    'day' : '', 'type':'', 'times':[], 'hour':'', 'num': '', 'values': [], 'time':''
  }}]);
const updateType = (item:any, clear: boolean) => {
    if (clear) {
        item.config.values = [];
    }
    if (item.config.type === '1') {
        item.op1 = options.value
    } else if (item.config.type === '2') {
        item.op1 = staroptions.value
    }
  };
const fetchData = async () => {
  try {
    const { data } = await getAssignConfig();
    options.value = data.followStatus
    staroptions.value = data.starStatus
    tableData.value = data.list;
    updateType(tableData.value[2], false)
    updateType(tableData.value[3], false)
    updateType(tableData.value[4], false)
    updateType(tableData.value[5], false)
    updateType(tableData.value[6], false)
  } catch (err) {
    //
  }
};
fetchData();


  const submitHandler = async () => {
  try {
    const { data } = await saveAssignConfigStatus(tableData.value);
    Message.success({
            content: '操作成功',
            duration: 5 * 1000,
        })
  } catch (err) {
    //
  }
  }
</script>
<style scoped lang="less">
.container {
    padding: 0 20px 20px 20px;
}
.container input {
    overflow: visible;
    line-height: 24px;
    width: 37px;
    margin: 0px 5px;
    text-align: center;
}
.actions {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: 60px;
    padding: 14px 20px 14px 0;
    background: var(--color-bg-2);
    text-align: center;
}
</style>