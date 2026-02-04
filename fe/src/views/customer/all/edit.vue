<template>
  <div class="container">
    <Breadcrumb :items="['menu.customer', 'menu.customer.genjin', (route.params.id ?
       ((route.name == 'CustomerPreview' ? '查看' : '编辑') + '客户(id-'+route.params.id+')') : 
       ( route.params.introid ? '转介绍(' + route.params.introname + ')' : '录入客户')
    )]" />

    <a-card class="general-card" >
        <a-tabs v-model:active-key="activekey">
            <a-tab-pane key="1" title="基本信息" >
              <BaseInfo :activekey="activekey"/>
            </a-tab-pane>
            <a-tab-pane key="2" title="历史跟进记录"  v-if="userStore.$state.buttonPermission.includes('CustomerFollowList')" destroy-on-hide :lazy-load="false">
              <FollowInfo/>
            </a-tab-pane>
            <a-tab-pane key="3" title="客户流转记录"  v-if="userStore.$state.buttonPermission.includes('CustomerAssignList')" lazy-load destroy-on-hide >
              <AssignInfo/>
            </a-tab-pane>
            <a-tab-pane key="4" title="星级变化"   v-if="userStore.$state.buttonPermission.includes('CustomerStarList')" lazy-load destroy-on-hide >
              <StarInfo/>
            </a-tab-pane>
            <a-tab-pane key="5" title="回款情况"  v-if="userStore.$state.buttonPermission.includes('CustomerBackList')" lazy-load destroy-on-hide>
              <BackInfo/>
            </a-tab-pane>
            <a-tab-pane key="6" title="通话记录"  v-if="userStore.$state.buttonPermission.includes('CustomerCallList')" lazy-load destroy-on-hide>
              <CallInfo/>
            </a-tab-pane>
        </a-tabs>
    </a-card>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { useAppStore } from '@/store';
import { useRouter, useRoute } from 'vue-router';

import BaseInfo from './components/base-info.vue';
import AssignInfo from './components/assign-list.vue';
import FollowInfo from './components/follow-list.vue';
import StarInfo from './components/star-list.vue';
import BackInfo from './components/back-list.vue';
import CallInfo from './components/call-list.vue';

const route = useRoute();
const userStore = useAppStore();
const activekey = ref();
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
</style>
