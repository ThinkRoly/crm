import { DEFAULT_LAYOUT } from '@/router/constants';

export default {
  path: '/customer',
  name: 'customer',
  component: DEFAULT_LAYOUT,
  children: [
    {
      path: 'list',
      name: 'CustomerList',
      component: () => import('@/views/customer/all/list.vue'),
    },
    {
      path: 'mylist',
      name: 'MyCustomerList',
      component: () => import('@/views/customer/all/list.vue'),
    },
    {
      path: 'importlist',
      name: 'CustomerImportList',
      component: () => import('@/views/customer/all/list.vue'),
    },
    {
      path: 'newlist',
      name: 'CustomerNewList',
      component: () => import('@/views/customer/all/list.vue'),
    },
    {
      path: 'innerlist',
      name: 'CustomerInnerList',
      component: () => import('@/views/customer/all/list.vue'),
    },
    {
      path: 'analyst',
      name: 'CustomerAnalyst',
      component: () => import('@/views/data/analyst.vue'),
    },
    {
      path: 'newpool',
      name: 'CustomerNewpool',
      component: () => import('@/views/customer/all/list.vue'),
    },
    {
      path: 'poold',
      name: 'CustomerPool',
      component: () => import('@/views/customer/all/list.vue'),
    },
    {
      path: 'unvalid',
      name: 'CustomerUnvalid',
      component: () => import('@/views/customer/all/list.vue'),
    },
    {
      path: 'edit/:id?/:allIds?',
      name: 'CustomerEdit',
      component: () => import('@/views/customer/all/edit.vue'),
    },
    {
      path: 'perview/:id?',
      name: 'CustomerPreview',
      component: () => import('@/views/customer/all/edit.vue'),
    },
    {
      path: 'follow/:id?/:allIds?',
      name: 'CustomerFollow',
      component: () => import('@/views/customer/all/edit.vue'),
    },
  ],
};
