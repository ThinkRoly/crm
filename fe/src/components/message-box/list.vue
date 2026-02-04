<template>
  <a-list :bordered="false">
    <a-list-item v-for="item in renderList" :key="item.id" action-layout="vertical" :style="{
      opacity: item.status ? 0.5 : 1,
    }">
      <div class="item-wrap" >
        <a-list-item-meta>
          <template #title>
            <a-space :size="4">
              <span >{{ item.title }} 
              <a-button type="text" size="small" @click="genjin(item)">
                去跟进
              </a-button>
              
              </span>
            </a-space>
          </template>
          <template #description>
            <div>
              <a-typography-paragraph :ellipsis="{ rows: 1, }">{{ item.content }}</a-typography-paragraph>
              <a-typography-text class="time-text">
                {{ item.time }}
              </a-typography-text>
            </div>
          </template>
        </a-list-item-meta>
      </div>
    </a-list-item>
  </a-list>
</template>

<script lang="ts" setup>
import { PropType } from 'vue';
import { MessageRecord, MessageListType } from '@/api/message';
import { useRouter } from 'vue-router';

const router = useRouter();
const props = defineProps({
  renderList: {
    type: Array as PropType<MessageListType>,
    required: true,
  },
  unreadCount: {
    type: Number,
    default: 0,
  },
});
const emit = defineEmits(['itemClick']);
const allRead = () => {
  emit('itemClick', [...props.renderList]);
};

const onItemClick = (item: MessageRecord) => {
  if (!item.status) {
    emit('itemClick', [item]);
  }
};

const genjin = (item: MessageRecord) => {
  onItemClick(item)
  const routeUrl = router.resolve({
    name: 'CustomerEdit',
    params: { 'id':item.customId }
  })
  window.open(routeUrl.href, "_blank")
}
const showMax = 3;
</script>

<style scoped lang="less">
:deep(.arco-list) {
  .arco-list-item {
    min-height: 86px;
    border-bottom: 1px solid rgb(var(--gray-3));
  }

  .arco-list-item-extra {
    position: absolute;
    right: 20px;
  }

  .arco-list-item-meta-content {
    flex: 1;
  }

  .item-wrap {
    cursor: pointer;
  }

  .time-text {
    font-size: 12px;
    color: rgb(var(--gray-6));
  }

  .arco-empty {
    display: none;
  }

  .arco-list-footer {
    padding: 0;
    height: 50px;
    line-height: 50px;
    border-top: none;

    .arco-space-item {
      width: 100%;
      border-right: 1px solid rgb(var(--gray-3));

      &:last-child {
        border-right: none;
      }
    }

    .add-border-top {
      border-top: 1px solid rgb(var(--gray-3));
    }
  }

  .footer-wrap {
    text-align: center;
  }

  .arco-typography {
    margin-bottom: 0;
  }

  .add-border {
    border-top: 1px solid rgb(var(--gray-3));
  }
}
</style>
