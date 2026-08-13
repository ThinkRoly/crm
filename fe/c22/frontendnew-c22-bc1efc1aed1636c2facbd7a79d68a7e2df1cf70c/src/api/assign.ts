import axios from 'axios';

export function updateAssignInfo(id:number, config: any) {
  return axios.post('/api/operate/assign/edit', { id, config
  });
}

export function getAssignConfig() {
  return axios.get('/api/operate/assign/config', { });
}

export function updateAssignConfig(id: number, value: string, key: string) {
  return axios.post('/api/operate/assign/editrule', { id, value, key});
}


export function setAssignConfigStatus(id: number, status: number) {
  return axios.post('/api/operate/assign/setstatus', { id, status});
}


export function saveAssignConfigStatus(data: any) {
  return axios.post('/api/operate/assign/editrule', { 'rule': data });
}