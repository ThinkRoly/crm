<template>
  <div class="content">
    <a-result class="result" status="404" subtitle="无权限查看当前页面，请联系系统管理员.....">
    </a-result>
    <div class="operation-row">
      <a-button key="back" type="primary" @click="back"> 回到首页 </a-button>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import { useRouter } from 'vue-router';

  const router = useRouter();
  const back = () => {
    // warning： Go to the node that has the permission
    router.push({ name: 'Workplace' });
  };
</script>

<style scoped lang="less">
  .content {
    // padding-top: 100px;
    top: 50%;
    left: 50%;
    text-align: center;
  }
</style>
