import { DEFAULT_LAYOUT } from '@/router/constants';

export default {
  path: '/data',
  name: 'data',
  component: DEFAULT_LAYOUT,
  children: [
    {
      path: 'channel',
      name: 'ChannelData',
      component: () => import('@/views/data/channel.vue'),
    },
    {
      path: 'channelAnalyst',
      name: 'ChannelAnalyst',
      component: () => import('@/views/data/channelAnalyst.vue'),
    },
    {
      path: 'channeldetail',
      name: 'ChannelDataDetail',
      component: () => import('@/views/data/channelDetail.vue'),
    },
    {
      path: 'success',
      name: 'Success',
      component: () => import('@/views/data/success.vue'),
    },
    {
      path: 'mydata',
      name: 'MyData',
      component: () => import('@/views/data/my.vue'),
    },
    {
      path: 'work',
      name: 'Work',
      component: () => import('@/views/data/work.vue'),
    },
    {
      path: 'cost',
      name: 'Cost',
      component: () => import('@/views/data/cost.vue'),
    },
  ]
}