import axios from 'axios';
import qs from 'query-string';

export interface ChannelInfo {
  name : string;
  star1 : string;
  star2 : string;
  star3 : string;
  star4 : string;
  star5 : string;
  star3plus : string;
  star1_num : string;
  star2_num : string;
  star3_num : string;
  star4_num : string;
  star5_num : string;
  star3plus_num : string;
  follow_user_id: string;
}


export interface ChannelList {
  list: ChannelInfo[],
  total: number,
  sourceList: [],
  allTeamListSelect: [],
  followUserList: [],
}

export interface ChannelListParams extends Partial<ChannelInfo> {
  current: number;
  pageSize: number;
  timeType: string;
  type: string;
}

export function queryChannelList(params:ChannelListParams) {
  return axios.get<ChannelList>('/api/channel/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}


export interface BackInfo {
  name: string;
}


export interface BackList {
  list: BackInfo[],
  total: number,
  allTeamListSelect: [],
  followUserList: [],
}

export interface BackListParams extends Partial<BackInfo> {
  current: number;
  pageSize: number;
}

export function queryBackList(params:BackListParams) {
  return axios.get<BackList>('/api/data/backlist', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}


export function editBacks(back: any) {
  return axios.post('/api/custom/editback', {
    ...back
  });
}

export function delBack(id: any) {
  return axios.post('/api/custom/delback', {id});
}

export function queryWorkList(params:ChannelListParams) {
  return axios.get('/api/work/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function queryCostList(params:ChannelListParams) {
  return axios.get('/api/cost/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}


export function queryReportList(params:any) {
  return axios.get('/api/data/reportlist', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function queryAnalystList(params:any) {
  params['times[]'] = params.time
  console.log(params
    )
  return axios.get('/api/data/analyst', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}


export function queryChannelAnalystList(params:ChannelListParams) {
  return axios.get<ChannelList>('/api/data/channelanalyst', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}


export function queryWorkUserList(params:any) {
  console.log(params);
  return axios.get('/api/data/workuserlist', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}