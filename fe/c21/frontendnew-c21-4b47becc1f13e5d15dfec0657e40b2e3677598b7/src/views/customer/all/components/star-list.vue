<template>
    <div class="container baseinfo">
        <a-table row-key="id" :loading="loading"
            :pagination="pagination" :data="renderData" :bordered="false" label-align="center" @page-change="onPageChange">
            <template #columns>
                <a-table-column title="记录id" data-index="id" :width="80" />
                <a-table-column title="旧星级" data-index="old_value" :width="180" />
                <a-table-column title="新星级" data-index="new_value" :width="100" />
                <a-table-column title="操作人" data-index="oper_user" :width="120"> </a-table-column>
                <a-table-column title="时间" data-index="create_time" :width="180"> </a-table-column>
            </template>
        </a-table>
    </div>
</template> 

<script lang="ts" setup>
import { useRouter, useRoute } from 'vue-router';
import { computed, ref, reactive } from 'vue';
import useLoading from '@/hooks/loading';
import { queryCustomStarList } from '@/api/custom';
import { Pagination } from '@/types/global';


const { loading, setLoading } = useLoading(true);
const renderData = ref([]);
const basePagination: Pagination = { current: 1, pageSize: 20, };
const pagination = reactive({ ...basePagination, });
const route = useRoute();
const id = ref(route.params.id as string);


const fetchData = async (
    params: Pagination,
) => {
    setLoading(true);
    try {
        const { data } = await queryCustomStarList({...params, customId:id.value});
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