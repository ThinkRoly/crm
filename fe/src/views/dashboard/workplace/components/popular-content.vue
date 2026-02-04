<template>
  <a-spin :loading="loading" style="width: 100%">
    <a-card
      class="general-card"
      :header-style="{ paddingBottom: '0' }"
      :body-style="{ padding: '17px 20px 21px 20px' }"
    >
      <template #title>
        {{ $t('workplace.popularContent') }}
      </template>
      <a-space direction="vertical" :size="10" fill>
        <a-table
          :data="paihangbang"
          :pagination="false"
          :bordered="false"
          :scroll="{ x: '100%', y: '264px' }"
        >
          <template #columns>
            <a-table-column title="排名" data-index="index" :width="60"></a-table-column>
            <a-table-column title="姓名" data-index="user_name" v-if="userStore.$state.buttonPermission.includes('YejiName')">
            </a-table-column>
            <a-table-column title="团队" data-index="team_name" v-if="userStore.$state.buttonPermission.includes('YejiTeam')">
            </a-table-column>
            <a-table-column title="金额" data-index="amount" v-if="userStore.$state.buttonPermission.includes('YejiAmount')">
            </a-table-column>
            <a-table-column title="创收" data-index="real_amount" v-if="userStore.$state.buttonPermission.includes('YejiRealAmount')">
            </a-table-column>
          </template>
        </a-table>
      </a-space>
    </a-card>
  </a-spin>
</template>

<script lang="ts" setup>
  import { ref } from 'vue';
  import useLoading from '@/hooks/loading';
  import type { TableData } from '@arco-design/web-vue/es/table/interface';
  import { useAppStore } from '@/store';

const userStore = useAppStore();

const props = defineProps({
  paihangbang : {
    type: Array as () => TableData[],
    required: true,
  }
});
  const type = ref('text');
  const { loading, setLoading } = useLoading();
  const renderList = ref<TableData[]>();
  const fetchData = async (contentType: string) => {
    try {
      setLoading(true);
    } catch (err) {
      // you can report use errorHandler or other
    } finally {
      setLoading(false);
    }
  };
  const typeChange = (contentType: string) => {
    fetchData(contentType);
  };
  fetchData('text');
</script>

<style scoped lang="less">
  .general-card {
    min-height: 395px;
  }
  :deep(.arco-table-tr) {
    height: 44px;
    .arco-typography {
      margin-bottom: 0;
    }
  }
  .increases-cell {
    display: flex;
    align-items: center;
    span {
      margin-right: 4px;
    }
  }
</style>
