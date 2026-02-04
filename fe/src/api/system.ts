import axios from 'axios';
import qs from 'query-string';
import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';

export interface UserInfo {
  id: string;
  mobile: string;
  name: string;
  password: string;
  roles: number[];
  parent_id: string;
  team_id: string;
  status: string;
  createdTime: string;
  online: string;
}

export interface RoleInfo {
  id: string;
  name: string;
  status: string;
  views : string;
  rights: number[];
  halfrights: number[];
  fields1: number[];
  fields2: number[];
  fields3: number[];
}


export interface TeamInfo {
  id: string;
  name: string;
  status: string;
  manager_id: string;
  rights: number[];
  parent_id: string;
}

export interface UserListParams extends Partial<UserInfo> {
  current: number;
  pageSize: number;
}

export interface RoleListParams extends Partial<RoleInfo> {
  current: number;
  pageSize: number;
}

export interface TeamListParams extends Partial<TeamInfo> {
  current: number;
  pageSize: number;
}

export interface UserList {
  list: UserInfo[];
  allTeamListSelect: [];
  total: number;
  newlimit: number;
  newnow: number;
  newnow2: number;
  innerlimit: number;
  innernow : number;
}


export interface RoleList{
  list: RoleInfo[];
  total: number;
}

export interface TeamList {
  list: TeamInfo[];
  total: number;
}

export interface UserInfoDetail {
  userInfo: UserInfo;
  allUserListSelect: [];
  allUserId: [];
  allTeamListSelect: [];
  roleList: RoleInfo[];
  userTree: [];
}


export interface RoleInfoDetail {
  roleInfo: RoleInfo;
  selectFieldList1: SelectOptionData[];
  selectFieldList2: SelectOptionData[];
  selectFieldList3: SelectOptionData[];
  selectRightList: [];
}

export interface TeamInfoDetail {
  teamInfo: TeamInfo;
  allUserListSelect: [];
  allTeamListSelect: [];
}


export function queryUserList(params: UserListParams) {
  return axios.get<UserList>('/api/system/user/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function editUserInfo(params: UserInfo) {
  return axios.post<UserList>('/api/system/user/edit', params);
}

export function lockUser(id: string, status: string) {
  return axios.post<UserList>('/api/system/user/lock', {
    id,
    status,
  });
}


export function deleteUser(id: string) {
  return axios.post<UserList>('/api/system/user/delete', {
    id
  });
}
export function resetUserPwd(id: string) {
  return axios.post<UserList>('/api/system/user/resetpwd', {
    id,
  });
}



export function getUserInfo(id: string) {
  return axios.post<UserInfoDetail>('/api/system/user/info', { id });
}

export function updateUserRole(id: string, roles: number[]) {
  return axios.post('/api/system/user/role', { id, roles });
}


export function queryRoleList(params: RoleListParams) {
  return axios.get<RoleList>('/api/system/role/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function editRoleInfo(params: RoleInfo) {
  return axios.post('/api/system/role/edit', params);
}

export function lockRole(id: string, status: string) {
  return axios.post('/api/system/role/lock', { id, status, });
}

export function getRoleInfo(id: string) {
  return axios.post<RoleInfoDetail>('/api/system/role/info', { id });
}


export function queryTeamList(params: TeamListParams) {
  return axios.get<TeamList>('/api/system/team/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function editTeamInfo(params: TeamInfo) {
  return axios.post('/api/system/team/edit', params);
}


export function getTeamInfo(id: string) {
  return axios.post<TeamInfoDetail>('/api/system/team/info', { id });
}
 
export function delTeam(id: string) {
  return axios.post<TeamInfoDetail>('/api/system/team/del', { id });
}

export function deleteRole(id: string) {
  return axios.post<TeamInfoDetail>('/api/system/role/delete', { id });
}


export function queryProductList(params: any) {
  return axios.get('/api/system/product/list', {
    params,
    paramsSerializer: (obj) => {
      return qs.stringify(obj);
    },
  });
}

export function editProductInfo(params: any) {
  return axios.post('/api/system/product/edit', params);
}


export function getProductInfo(id: string) {
  return axios.post('/api/system/product/info', { id });
}

export function ooUser(id: string, status: string) {
  return axios.post('/api/system/user/onoffline', { id, status, });
}