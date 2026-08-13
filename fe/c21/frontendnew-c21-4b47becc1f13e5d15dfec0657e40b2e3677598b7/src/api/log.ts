import axios from 'axios';
import qs from 'query-string';
import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';

export interface LogInfo {
  objId: string;
  before: string;
  after: string;
  user: string;
  name : string;
  remark: string;
  time: string;
}


export interface LogListParams extends Partial<LogInfo> {
  current: number;
  pageSize: number;
  url : string;
  id : number;
}


export interface LogList{
  list: LogInfo[];
  total: number;
}


export function queryLogList(params: LogListParams) {
  return axios.get<LogList>(params.url, {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}
