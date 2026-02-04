import { DEFAULT_LAYOUT } from '@/router/constants';

export default {
  path: '/system',
  name: 'system',
  component: DEFAULT_LAYOUT,
  children: [
    {
      path: 'usersetting',
      name: 'Usersetting',
      component: () => import('@/views/user/setting.vue'),
    },
    {
      path: 'user',
      name: 'User',
      component: () => import('@/views/user/list.vue'),
    },
    {
      path: 'useredit/:id?',
      name: 'Useredit',
      component: () => import('@/views/user/edit.vue'),
    },
    {
      path: 'userpreview/:id?',
      name: 'Userpreview',
      component: () => import('@/views/user/edit.vue'),
    },
    {
      path: 'userrole/:id?',
      name: 'Userrole',
      component: () => import('@/views/user/role.vue'),
    },
    {
      path: 'role',
      name: 'Role',
      component: () => import('@/views/role/list.vue'),
    },
    {
      path: 'roleedit/:id?',
      name: 'Roleedit',
      component: () => import('@/views/role/edit.vue'),
    },
    {
      path: 'Rolepreview/:id?',
      name: 'Rolepreview',
      component: () => import('@/views/role/edit.vue'),
    },
    {
      path: 'team',
      name: 'Team',
      component: () => import('@/views/team/list.vue'),
    },
    {
      path: 'teamedit/:id?',
      name: 'Teamedit',
      component: () => import('@/views/team/edit.vue'),
    },
    {
      path: 'teampreview/:id?',
      name: 'Teampreview',
      component: () => import('@/views/team/edit.vue'),
    },
    {
      path: 'noticelist',
      name: 'NoticeList',
      component: () => import('@/views/notice/list.vue'),
    },
    {
      path: 'working',
      name: 'Working',
      component: () => import('@/views/not-found/index.vue'),
    },
    {
      path: 'setting',
      name: 'Setting',
      component: () => import('@/views/setting/index.vue'),
    },
    {
      path: 'approvemy',
      name: 'ApproveMy',
      component: () => import('@/views/approve/components/mylist.vue'),
    },
    {
      path: 'approve',
      name: 'ApproveOther',
      component: () => import('@/views/approve/components/otherlist.vue'),
    },
     {
      path: 'product',
      name: 'Product',
      component: () => import('@/views/product/list.vue'),
    },
    {
      path: 'productedit/:id?',
      name: 'ProductEdit',
      component: () => import('@/views/product/edit.vue'),
    },
    { 
      path: 'productview/:id?',
      name: 'ProductView',
      component: () => import('@/views/product/edit.vue'),
    },
  ],
};
