import axios from 'axios';

export function queryList(params: any) {
  return axios.get('/api/approve/list', {
    'params' : params,
  });
}


export function cancelApprove(id: any) {
  return axios.post('/api/approve/cancel', {
    id
  });
}

export function passApprove(id: any, status: any) {
  return axios.post('/api/approve/pass', {
    id , status 
  });
}

export function getApprove(id: any) {
  return axios.post('/api/approve/view', {
    id
  });
}