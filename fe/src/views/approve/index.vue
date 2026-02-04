<template>
  <div class="container">
    <Breadcrumb :items="['menu.customer', 'menu.approve']" />

    <a-card class="general-card" >
        <a-tabs v-model:active-key="activekey">
            <a-tab-pane key="1" title="我发起的审批" >
              <MyList />
            </a-tab-pane>
            <a-tab-pane key="2" title="我处理的审批">
              <OtherList />
            </a-tab-pane>
        </a-tabs>
    </a-card>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';

import MyList from './components/mylist.vue';
import OtherList from './components/otherlist.vue';

const activekey = ref();
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
</style>
