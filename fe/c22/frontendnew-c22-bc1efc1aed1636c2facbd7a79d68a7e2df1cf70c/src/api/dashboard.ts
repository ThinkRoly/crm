import axios from 'axios';
import qs from 'query-string';

export interface ContentDataRecord {
  x: string;
  y: number;
}

export function queryContentData() {
  return axios.get('/api/user/dashboard');
}

export interface NoticeInfo {
  id: number;
  type: string;
  remark: string;
  status: 0 | 1;
}

export interface NoticeList {
  list: NoticeInfo[];
  allTeamListSelect: [];
  total: number;
}


export interface NoticeListParams extends Partial<NoticeInfo> {
  current: number;
  pageSize: number;
}

export function queryNoticeList(params: NoticeListParams) {
  return axios.get<NoticeList>('/api/message/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

interface MessageStatus {
  ids: number[];
}

export function setMessageStatus(data: MessageStatus) {
  return axios.post('/api/message/read', data);
}
