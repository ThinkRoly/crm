<template>
    <div class="container baseinfo">
        <a-table row-key="id" :loading="loading"
            :pagination="pagination" :data="renderData" :bordered="false" label-align="center" @page-change="onPageChange">
            <template #columns>
                <a-table-column title="记录id" data-index="id" :width="80" />
                <a-table-column title="跟进人" data-index="oper_user" :width="120"> </a-table-column>
                <a-table-column title="时长" data-index="total_duration" :width="180" />
                <a-table-column title="通话状态" data-index="status_name" :width="100" />
                <a-table-column title="时间" data-index="create_time" :width="180"> </a-table-column>
                <a-table-column title="操作" data-index="oper_user1" :width="120"> 
                    <template #cell="{ record }">
                    <a-button 
                        v-if="userStore.$state.buttonPermission.includes('CustomerCallRecord') && record.status == 1" type="text" size="small"
                      @click="recordshow=true;recordsrc=record.file;
                      ">
                      <icon-eye /> 查看录音
                    </a-button>
                    </template>
                </a-table-column>
            </template>
        </a-table>
        <a-modal v-model:visible="recordshow" title="播放录音">
            <embed :src="recordsrc" />
        </a-modal>
    </div>
</template> 

<script lang="ts" setup>
import { useRouter, useRoute } from 'vue-router';
import { computed, ref, reactive } from 'vue';
import useLoading from '@/hooks/loading';
import { queryCustomCallList } from '@/api/custom';
import { Pagination } from '@/types/global';
import { useAppStore } from '@/store';


const { loading, setLoading } = useLoading(true);
const userStore = useAppStore();
const renderData = ref([]);
const basePagination: Pagination = { current: 1, pageSize: 20, };
const pagination = reactive({ ...basePagination, });
const route = useRoute();
const id = ref(route.params.id as string);
const recordshow = ref(false);
const recordsrc = ref("");


const fetchData = async (
    params: Pagination,
) => {
    setLoading(true);
    try {
        const { data } = await queryCustomCallList({...params, customId:id.value});
        renderData.value = data.list;
        pagination.current = params.current;
        pagination.total = data.total;
    } catch (err) {
        //
    } finally {
        setLoading(false);
    }
};

const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current });
};

fetchData( { current: 1, pageSize: 20 });


</script>

<style scoped lang="less">
.container {
    padding: 0 20px 20px 20px;
}
</style>