import { DEFAULT_LAYOUT } from '@/router/constants';

export default {
  path: '/operate',
  name: 'operate',
  component: DEFAULT_LAYOUT,
  children: [
    {
      path: 'channel',
      name: 'Channel',
      component: () => import('@/views/operate/channel.vue'),
    },
    {
      path: 'channeledit/:id?',
      name: 'ChannelEdit',
      component: () => import('@/views/operate/edit.vue'),
    },
    {
      path: 'channelview/:id?',
      name: 'ChannelView',
      component: () => import('@/views/operate/edit.vue'),
    },
    {
      path: 'Assign',
      name: 'Assign',
      component: () => import('@/views/operate/assign.vue'),
    },
    {
      path: 'follow',
      name: 'Follow',
      component: () => import('@/views/operate/follow.vue'),
    },
  ]
}