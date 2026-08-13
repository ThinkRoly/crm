import axios from 'axios';
import qs from 'query-string';


export function getChannelConfig(data:any) {
  return axios.get('/api/operate/channel/config', {
    params : data
  });
}

export function getChannelInfo(id: string) {
  return axios.post('/api/operate/channel/info', { id });
}

export function editChannelInfo(data: any) {
  return axios.post('/api/operate/channel/edit', data );
}

export function setChannelStatus(id: string, status: string) {
  return axios.post('/api/operate/channel/setstatus', {id, status});
}