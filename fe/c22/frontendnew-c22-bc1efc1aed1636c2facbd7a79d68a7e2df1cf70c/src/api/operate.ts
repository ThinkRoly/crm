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
}


export interface ChannelList {
  list: ChannelInfo[]
}

export interface ChannelListParams extends Partial<ChannelInfo> {
  current: number;
  pageSize: number;
}

export function queryChannelList(params:ChannelListParams) {
  return axios.get<ChannelList>('/api/channel/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}
