import axios from 'axios';
import qs from 'query-string';
import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
import { ref } from 'vue';

const formModel = ref({ 
  name: '',
  mobile: '',
  city: '',
  followUserId: '',
  lastFollowUserId: '',
  followStatus: '',
  star: '',
  team_id: '',
  userFrom: '',
  source: '',
  remark: '',
  zizhi: [],
  createdTime: [],
  followTime: [],
  giveupTime: [],
  notFollow: '',
  minFt: '',
  maxFt: '',
});

export const initFormModel = () => {
  formModel.value = { 
    name: '',
    mobile: '',
    city: '',
    followUserId: '',
    lastFollowUserId: '',
    followStatus: '',
    star: '',
    team_id: '',
    remark: '',
    userFrom: '',
    source: '',
    zizhi: [],
    createdTime: [],
    followTime: [],
    giveupTime: [],
    notFollow: '',
    minFt: '',
    maxFt: '',
  }
}

export const getFormModel = () => {
  return formModel.value 
}

const page = ref(1);

export const setPage = (p: number) => {
  page.value = p
}
export const getPage = () => {
  return page.value 
}


const pageSize = ref(20);

export const setPageSize = (p: number) => {
  pageSize.value = p
}
export const getPageSize = () => {
  return pageSize.value 
}

export interface NoticeInfo {
  date: string,
  remark: string,
  type: number
}

export interface CustomInfo {
  id: string;
  mobile: string;
  name: string;
  live_area: string,
  live_address: string,
  notices: NoticeInfo[],
  sex: string,
  age: string,
  household_area: string,
  household_address:string,
  marry: string,
  work: string,
  company: string,
  income: string,
  follow_status: string,
  star : string,
  house: string,
  policy: string,
  status: string,
  car: string,
  wage:string,
  funds:string,
  insurance:string,
  credit:string,
  qualification:string,
  remark: string,
  city: string;
  important: number;
  lock: number;
  amount: string;
  introid: string ;
  credit_detail: string;
  family: string;
}


export interface CustomListParams extends Partial<CustomInfo> {
  current: number;
  pageSize: number;
  type: string;
}


export interface CustomList {
  cityList: SelectOptionData[];
  followUserList: SelectOptionData[];
  followStatusList : SelectOptionData[];
  starList : SelectOptionData[];
  sourceList : SelectOptionData[];
  userFromList : SelectOptionData[];
  zizhiList : SelectOptionData[];
  teamList : SelectOptionData[];
  list: CustomInfo[];
  customIds: [];
  title: string;
  total: number;
  master: number;
}


export interface CustomInfoDetail {
  customInfo: CustomInfo;
  allCustomListSelect: [];
  workList: SelectOptionData[];
  followStatusList : SelectOptionData[];
  starList : SelectOptionData[];
  cityList: SelectOptionData[];
  noticesList:SelectOptionData[];
  quanzhengList:SelectOptionData[];
  productList:SelectOptionData[];
  logList: [];
  createTime: '';
  fromInfo :'';
}


export interface CustomAssignList {
  list: [];
  total: number;
}

export function queryCustomList(params: CustomListParams) {
  return axios.get<CustomList>('/api/custom/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function queryChannelCustomList(params: any) {
  return axios.get<CustomList>('/api/channel/detail', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function queryReportCustomList(params: any) {
  return axios.get<CustomList>('/api/data/reportdetail', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function editCustomInfo(params: CustomInfo) {
  return axios.post('/api/custom/edit', params);
}

export function getCustomInfo(id: string) {
  return axios.post<CustomInfoDetail>('/api/custom/info', { id });
}

export function assignCustomInfo(followUserId: number, customIds : number[] ) {
  return axios.post<CustomInfoDetail>('/api/custom/assign', { followUserId, customIds});
}

export function queryCustomAssignList(params: any) {
  return axios.get<CustomAssignList>('/api/custom/assignlist', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function queryCustomFollowList(params: any) {
  return axios.get<CustomAssignList>('/api/custom/followlist', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}


export function queryCustomStarList(params: any) {
  return axios.get<CustomAssignList>('/api/custom/starlist', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}


export function queryCustomCallList(params: any) {
  return axios.get<CustomAssignList>('/api/custom/calllist', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}



export function queryCustomBackList(params: any) {
  return axios.get<CustomAssignList>('/api/custom/backlist', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}


export function laheiCustom(id: string, status: string) {
  return axios.post('/api/custom/lahei', {
    id,
    status,
  });
}

export function customImportant(id: string, status: number) {
  return axios.post('/api/custom/important', {
    id,
    important: status,
  });

}


export function customLock(id: string, status: number) {
  return axios.post('/api/custom/lock', {
    id,
    lock: status,
  });

}


export function customGiveup(id: string) {
  return axios.post('/api/custom/giveup', {
    id
  });

}


export function getNoticeList(id: string) {
  return axios.post('/api/custom/getnoticelist', {
    id
  });

}

export function submitNotices(id:string, notices:NoticeInfo[]) {
  return axios.post('/api/custom/addnotices', {
    id, notices
  });
}

export function submitBacks(id:string, back: any) {
  return axios.post('/api/custom/submitback', {
    id, 
    ...back
  });
}


export function customerGet(id:string) {
  return axios.post('/api/custom/get', {
    id, 
  });
}

export function CustomBatchGiveup(customIds : number[] ) {
  return axios.post<CustomInfoDetail>('/api/custom/batchgiveup', { customIds});
}
export function CustomBatchGet(customIds : number[] ) {
  return axios.post<CustomInfoDetail>('/api/custom/batchget', { customIds});
}

export function submitDianpings(id:string, back: any) {
  return axios.post('/api/custom/dianping', {
    id, 
    ...back
  });
}

export function makeCall(id:string) {
  return axios.post('/api/custom/call', {
    id, 
  });
}


export function queryCustomData(params: any) {
  return axios.get('/api/custom/data', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}