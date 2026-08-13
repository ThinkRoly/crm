<template>
  <div class="container">
    <div class="left-side">
      <div class="panel">
        <Banner />
        <a-grid :cols="24" :row-gap="16" class="panel">
            <a-grid-item
              class="panel-col"
              :span="{ xs: 8, sm: 8, md: 8, lg: 8, xl: 8, xxl: 8}"
            >
              <a-space>
                <a-avatar :size="54" class="col-avatar">
                  <img
                    alt="avatar"
                    src="//p3-armor.byteimg.com/tos-cn-i-49unhts6dw/288b89194e657603ff40db39e8072640.svg~tplv-49unhts6dw-image.image"
                  />
                </a-avatar>
                <a-statistic
                  title="我的客户"
                  :value="count.customer"
                  :precision="0"
                  :value-from="0"
                  animation
                  show-group-separator
                  @click="$router.push({'name':'CustomerList'})"
                  style="cursor: pointer;"
                >
                  <template #suffix>
                    <span class="unit">{{ $t('workplace.pecs') }}</span>
                  </template>
                </a-statistic>
              </a-space>
            </a-grid-item>
            <a-grid-item
              class="panel-col"
              :span="{ xs: 8, sm: 8, md: 8, lg: 8, xl: 8, xxl: 8}"
            >
              <a-space>
                <a-avatar :size="54" class="col-avatar">
                  <img
                    alt="avatar"
                    src="//p3-armor.byteimg.com/tos-cn-i-49unhts6dw/fdc66b07224cdf18843c6076c2587eb5.svg~tplv-49unhts6dw-image.image"
                  />
                </a-avatar>
                <a-statistic
                  title="待跟进客户"
                  :value="count.followcustomer"
                  :value-from="0"
                  animation
                  show-group-separator
                  @click="$router.push({'name':'CustomerList'})"
                  style="cursor: pointer;"
                >
                  <template #suffix>
                    <span class="unit">{{ $t('workplace.pecs') }}</span>
                  </template>
                </a-statistic>
              </a-space>
            </a-grid-item>
            <a-grid-item
              class="panel-col"
              :span="{ xs: 8, sm: 8, md: 8, lg: 8, xl: 8, xxl: 8}"
            >
              <a-space>
                <a-avatar :size="54" class="col-avatar">
                  <img
                    alt="avatar"
                    src="//p3-armor.byteimg.com/tos-cn-i-49unhts6dw/77d74c9a245adeae1ec7fb5d4539738d.svg~tplv-49unhts6dw-image.image"
                  />
                </a-avatar>
                <a-statistic
                  title="重要客户"
                  :value-from="0"
                  :value="count.important"
                  animation
                  show-group-separator
                  @click="$router.push({'name':'CustomerImportList'})"
                  style="cursor: pointer;"
                >
                  <template #suffix>
                    <span class="unit">{{ $t('workplace.pecs') }}</span>
                  </template>
                </a-statistic>
              </a-space>
            </a-grid-item>
            <a-grid-item
              class="panel-col"
              :span="{ xs: 8, sm: 8, md: 8, lg: 8, xl: 8, xxl: 8}"
              style="border-right: none"
            >
              <a-space>
                <a-avatar :size="54" class="col-avatar">
                  <img
                    alt="avatar"
                    src="//p3-armor.byteimg.com/tos-cn-i-49unhts6dw/c8b36e26d2b9bb5dbf9b74dd6d7345af.svg~tplv-49unhts6dw-image.image"
                  />
                </a-avatar>
                <a-statistic
                  title="新客户"
                  :precision="0"
                  :value-from="0"
                  :value="count.newcustomer"
                  animation
                  show-group-separator
                  @click="$router.push({'name':'CustomerNewList'})"
                  style="cursor: pointer;"
                >
                  <template #suffix>
                    <span class="unit">{{ $t('workplace.pecs') }}</span>
                  </template>
                </a-statistic>
              </a-space>
            </a-grid-item>
            <a-grid-item
              class="panel-col"
              :span="{ xs: 8, sm: 8, md: 8, lg: 8, xl: 8, xxl: 8}"
              style="border-right: none"
            >
              <a-space>
                <a-avatar :size="54" class="col-avatar">
                  <img
                    alt="avatar"
                    src="//p3-armor.byteimg.com/tos-cn-i-49unhts6dw/c8b36e26d2b9bb5dbf9b74dd6d7345af.svg~tplv-49unhts6dw-image.image"
                  />
                </a-avatar>
                <a-statistic
                  title="再分配客户"
                  :precision="0"
                  :value-from="0"
                  :value="count.innercustomer"
                  animation
                  show-group-separator
                  @click="$router.push({'name':'CustomerInnerList'})"
                  style="cursor: pointer;"
                >
                  <template #suffix>
                    <span class="unit">{{ $t('workplace.pecs') }}</span>
                  </template>
                </a-statistic>
              </a-space>
            </a-grid-item>
            <a-grid-item :span="24">
              <a-divider class="panel-border" />
            </a-grid-item>
          </a-grid>
      </div>
      <a-grid :cols="24" :col-gap="16" :row-gap="16" style="margin-top: 16px">
        <a-grid-item
          :span="{ xs: 15, sm: 15, md: 15, lg: 15, xl: 15, xxl: 15 }"
        >
          <PopularContent :paihangbang="paihangbang"/>
        </a-grid-item>
        <a-grid-item
          :span="{ xs: 9, sm: 9, md: 9, lg: 9, xl: 9, xxl: 9}"
        >
          <CategoriesPercent :data="chartData"/>
        </a-grid-item>
      </a-grid>
    </div>
    <div class="right-side">
      <a-grid :cols="24" :row-gap="20">
        <a-grid-item class="panel" :span="24">
          <Announcement :noticeList="noticeList"/>
        </a-grid-item>
      </a-grid>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import { computed, ref, reactive } from 'vue';
  import { queryContentData } from '@/api/dashboard';
  import Banner from './components/banner.vue';
  import PopularContent from './components/popular-content.vue';
  import CategoriesPercent from './components/categories-percent.vue';
  import Announcement from './components/announcement.vue';

  const chartData = ref();
  const noticeList = ref([]);
  const count = ref({
    'customer' : 0,
    'important' : 0,
    'followcustomer' : 0,
    'todaycustomer' : 0,
    'newcustomer' : 0,
    'innercustomer' : 0
  });
  const paihangbang = ref()
  const fetchData = async (
  ) => {
    try {
      const { data } = await queryContentData();
      noticeList.value = data.notice_list;
      count.value.customer = data.customer;
      count.value.important = data.important;
      count.value.followcustomer = data.followcustomer;
      count.value.newcustomer = data.newcustomer;
      count.value.innercustomer = data.innercustomer;
      paihangbang.value = data.paihangbang;
      chartData.value = data.chartData;
    } catch (err) {
      console.log(err);
    } 
  };


  fetchData();
</script>

<script lang="ts">
  export default {
    name: 'Dashboard', // If you want the include property of keep-alive to take effect, you must name the component
  };
</script>

<style lang="less" scoped>
  .container {
    background-color: var(--color-fill-2);
    padding: 16px 20px;
    padding-bottom: 0;
    display: flex;
  }

  .left-side {
    flex: 1;
    overflow: auto;
  }

  .right-side {
    width: 280px;
    margin-left: 16px;
  }

  .panel {
    background-color: var(--color-bg-2);
    border-radius: 4px;
    overflow: auto;
  }
  :deep(.panel-border) {
    margin-bottom: 0;
    border-bottom: 1px solid rgb(var(--gray-2));
  }
  .moduler-wrap {
    border-radius: 4px;
    background-color: var(--color-bg-2);
    :deep(.text) {
      font-size: 12px;
      text-align: center;
      color: rgb(var(--gray-8));
    }

    :deep(.wrapper) {
      margin-bottom: 8px;
      text-align: center;
      cursor: pointer;

      &:last-child {
        .text {
          margin-bottom: 0;
        }
      }
      &:hover {
        .icon {
          color: rgb(var(--arcoblue-6));
          background-color: #e8f3ff;
        }
        .text {
          color: rgb(var(--arcoblue-6));
        }
      }
    }

    :deep(.icon) {
      display: inline-block;
      width: 32px;
      height: 32px;
      margin-bottom: 4px;
      color: rgb(var(--dark-gray-1));
      line-height: 32px;
      font-size: 16px;
      text-align: center;
      background-color: rgb(var(--gray-1));
      border-radius: 4px;
    }
  }
</style>

<style lang="less" scoped>
  // responsive
  .mobile {
    .container {
      display: block;
    }
    .right-side {
      // display: none;
      width: 100%;
      margin-left: 0;
      margin-top: 16px;
    }
  }

  .arco-grid.panel {
    margin-bottom: 0;
    padding: 16px 20px 0 20px;
  }
  .panel-col {
    padding-left: 43px;
    border-right: 1px solid rgb(var(--gray-2));
  }
  .col-avatar {
    margin-right: 12px;
    background-color: var(--color-fill-2);
  }
  .up-icon {
    color: rgb(var(--red-6));
  }
  .unit {
    margin-left: 8px;
    color: rgb(var(--gray-8));
    font-size: 12px;
  }
  :deep(.panel-border) {
    margin: 4px 0 0 0;
  }
</style>