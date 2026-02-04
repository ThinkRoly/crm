import axios from 'axios';
import type { RouteRecordNormalized } from 'vue-router';
import { UserState } from '@/store/modules/user/types';

export interface LoginData {
  username: string;
  password: string;
}


export interface resetPwdData{
  oldpassword: string;
  newpassword: string;
  newpasswordConfrim: string;
}

export interface LoginRes {
  token: string;
  page: string;
}
export function login(data: LoginData) {
  return axios.post<LoginRes>('/api/user/login', data);
}

export function logout() {
  return axios.post<LoginRes>('/api/user/logout');
}

export function getUserInfo() {
  return axios.post<UserState>('/api/user/info');
}

export function getMenuList() {
  return axios.post<RouteRecordNormalized[]>('/api/user/menu');
}
 
export function resetPwd(data: resetPwdData) {
  return axios.post('/api/user/resetpassword', data);
}


export function switchOnline(online: any) {
  return axios.post('/api/user/online', {online});
}

export function getSetting() {
  return axios.get('/api/system/setting', {});
}

export function setSetting(data: any) {
  return axios.post('/api/system/setting', data);
}
