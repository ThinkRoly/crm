<template>
    <div>
        <a-table row-key="id"
          :loading="loading" :pagination="pagination" :data="renderData" :bordered="false" label-align="center"
          @page-change="onPageChange">
          <template #columns>
            <a-table-column title="id" data-index="id" :width="80"  />
            <a-table-column title="姓名" data-index="name" :width="100" >
              <template #cell="{ record }">
                <icon-lock v-if="record.lock == 1" style="margin-right:5px;" />{{ record.name }}
              </template>
            </a-table-column>
            <a-table-column title="跟进状态" data-index="follow_status" :width="100" >
              <template #cell="{ record }">
                <a-tag :color="record.fstatuscolor" v-if="record.fstatuscolor">{{ record.follow_status }}</a-tag>
              </template>
            </a-table-column>
            <a-table-column title="年龄" data-index="age" :width="60"  />
            <a-table-column title="城市" data-index="city" :width="60"  />
            <a-table-column title="申请金额" data-index="amount" :width="90"  />
            <a-table-column title="资质" data-index="zizhi" :width="160"  />
            <a-table-column title="星级" :width="60" >
              <template #cell="{ record }">
                <a-tag :color="record.starcolor" v-if="record.starcolor">{{ record.star }}</a-tag>
              </template>
            </a-table-column>
            <a-table-column title="跟进备注" data-index="remark" :width="200"  />
            <a-table-column title="分配后未跟进时间" data-index="notFollowTime" :width="145"  />
            <a-table-column title="跟进/分配/申请时间" :width="180" >
              <template #cell="{ record }">
                {{ record.follow_time }} <br />
                {{ record.assign_time }} <br />
                {{ record.apply_time }}
              </template>
  
            </a-table-column>
            <a-table-column title="跟进顾问" data-index="follow_user" :width="90"  />
            <a-table-column title="数据来源" data-index="source" :width="90"  />
            <a-table-column title="客户来源" data-index="user_from" :width="90"  />
          </template>
        </a-table>
    </div>
  </template>
  
  <script lang="ts" setup>
  import { ref, reactive } from 'vue';
  import useLoading from '@/hooks/loading';
  import {
    queryReportCustomList,
    CustomInfo,
    CustomListParams,
  } from '@/api/custom';
  import { Pagination } from '@/types/global';
  import { useRoute } from 'vue-router';
  
  
  const route = useRoute();
  const title = ref("");
  const params = ref({
    star: '',
    status: '',
    reportType: '',
    time1: '',
    time2: '',
    followUserId: '',
    userFrom: ''
  });
  
  const { loading, setLoading } = useLoading(true);
  const renderData = ref<CustomInfo[]>([]);
  const basePagination: Pagination = { current: 1, pageSize: 5 };
  const pagination = reactive({ ...basePagination });
  
  const fetchData = async (
    params1: CustomListParams = { current: 1, pageSize: 5, type: route.name as string}
  ) => {
    setLoading(true);
    try {
      const { data } = await queryReportCustomList({
        star : params.value.star,
        user_id: params.value.followUserId as string,
        status: params.value.status as string,
        reportType : params.value.reportType as string,
        time1 : params.value.time1 as string,
        time2 : params.value.time2 as string,
        userFrom : params.value.userFrom as string,
        current : params1.current,
        pageSize : params1.pageSize,
      });
      title.value = data.title
      renderData.value = data.list;
      pagination.current = params1.current;
      pagination.total = data.total;
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };
  const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current, type: route.name as string });
  };
  
  const fetchDataAll = (star: string, status:string , reportType:string , time1:'', time2:'', followUserId:string, userFrom: string) => {
    params.value.star = star 
    params.value.status = status 
    params.value.reportType = reportType
    params.value.time1 = time1 
    params.value.time2 = time2 
    params.value.followUserId = followUserId
    params.value.userFrom = userFrom 
    console.log(params.value)
    fetchData();
  }
  defineExpose({fetchDataAll})
  
  </script>
  