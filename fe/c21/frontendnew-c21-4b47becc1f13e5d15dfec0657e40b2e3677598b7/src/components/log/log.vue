<template>
    <a-table row-key="id" :loading="loading" :pagination="pagination" :data="renderData" :bordered="false"
        label-align="center" @page-change="onPageChange">
        <template #columns>
            <a-table-column title="修改前" data-index="before" />
            <a-table-column title="修改后" data-index="after" />
            <a-table-column title="备注" data-index="remark" />
            <a-table-column title="操作人" data-index="user" />
            <a-table-column title="操作时间" data-index="create_time" />
        </template>
    </a-table>
</template>

<script lang="ts" setup>
import { ref, reactive, computed, watch } from 'vue';
import useLoading from '@/hooks/loading';
import { queryLogList, LogInfo, LogListParams } from '@/api/log';
import { Pagination } from '@/types/global';

const props = defineProps({
    uri: {
        type: String,
        required: true,
    },
    id: {
        type: Number,
        default: 0,
    },
});
const { loading, setLoading } = useLoading(true);
const renderData = ref<LogInfo[]>([]);
const basePagination: Pagination = { current: 1, pageSize: 5, };
const pagination = reactive({ ...basePagination, });
const fetchData = async (
    params: LogListParams = { current: 1, pageSize: 5, url:'', id:0 }
) => {
    setLoading(true);
    try {
        params.url = props.uri;
        params.id = props.id;
        if (params.id <= 0) {
            return ;
        }
        const { data } = await queryLogList(params);
        renderData.value = data.list;
        pagination.current = params.current;
        pagination.total = data.total;
    } catch (err) {
        //
        console.log(err)
    } finally {
        setLoading(false);
    }
};

const idwatch = computed(() => {
    return props.id;
})

watch(idwatch, ()=>{
    renderData.value = [];
    fetchData();
    return props.id;
});

const search = () => {
    fetchData({
        ...basePagination,
    } as unknown as LogListParams);
};
const onPageChange = (current: number) => {
    fetchData({ ...basePagination, current , url:'', id:0});
};

fetchData();
</script>

<style scoped lang="less">
.container {
    padding: 0 20px 20px 20px;
}
</style>
