<template>
  <a-card
      class="general-card"
      :header-style="{ paddingBottom: '0' }"
      :body-style="{ padding: '17px 20px 21px 20px' }"
    >
    <template #title>
      日程提醒
    </template>
    <template #extra>
        <a-link @click="router.push({'name':'NoticeList'})">查看全部</a-link>
      </template>
  <a-timeline>
    <a-timeline-item :label="item.day" lineType="dashed" v-for="(item,index) in noticeList" :key="index">
      <a-tag size="small" :color="item.color">{{item.type}}</a-tag>
      <br />
      <a-typography-text
        type="secondary"
        :style="{ fontSize: '12px', marginTop: '4px', 'line-height': '28px' }"
      >
         {{item.remark}}
      </a-typography-text>
    </a-timeline-item>
  </a-timeline>
  </a-card>
</template>

<script lang="ts" setup>

import { useRouter, useRoute } from 'vue-router';

const router = useRouter();

const props = defineProps({
  noticeList : {
    type: Array as () => {
      'day' : '',
      'color':'',
      'type':'',
      'remark':''
    }[] ,
    required: true,
  }
});
</script>

<style scoped lang="less">
  .item {
    display: flex;
    align-items: center;
    width: 100%;
    height: 24px;
    margin-bottom: 4px;
    .item-content {
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      margin-left: 4px;
      color: var(--color-text-2);
      text-decoration: none;
      font-size: 13px;
      cursor: pointer;
    }
  }
</style>
