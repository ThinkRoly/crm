<template>
  <div class="container1 bigtable1">
    <Breadcrumb :items="['menu.customer', crumb]" />

    <a-card class="general-card" style="padding-top: 20px">
      <a-row>
        <a-col :flex="1">
          <a-form :model="formModel" label-align="left" auto-label-width>
            <a-row :gutter="16">
              <a-col :span="4">
                <a-form-item field="name" label="客户姓名">
                  <a-input v-model="formModel.name" placeholder="请输入" />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="mobile" label="客户电话">
                  <a-input v-model="formModel.mobile" placeholder="请输入" />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="status" label="城市">
                  <a-select
                    v-model="formModel.city"
                    :options="cityOptions"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="name" label="跟进顾问">
                  <a-select
                    v-model="formModel.followUserId"
                    v-if="route.name !== 'CustomerPool'"
                    :options="followUserOptions"
                    placeholder="请选择"
                    allow-search
                    allow-clear
                  />
                  <a-select
                    v-model="formModel.lastFollowUserId"
                    :options="followUserOptions"
                    v-if="route.name === 'CustomerPool'"
                    placeholder="请选择"
                    allow-search
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="mobile" label="跟进状态">
                  <a-select
                    v-model="formModel.followStatus"
                    :options="followStatusOptions"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
            </a-row>
            <a-row v-if="moreSelectOption" :gutter="16">
              <a-col :span="4">
                <a-form-item field="status" label="用户星级">
                  <a-select
                    v-model="formModel.star"
                    :options="starOptions"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="status" label="客户来源">
                  <a-select
                    v-model="formModel.userFrom"
                    :options="userFromOptions"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="status" label="渠道来源">
                  <a-select
                    v-model="formModel.source"
                    :options="sourceOptions"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="status" label="客户资质">
                  <a-select
                    v-model="formModel.zizhi"
                    multiple
                    :options="zizhiOptions"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="status" label="用户团队">
                  <a-tree-select
                    v-model="formModel.team_id"
                    :data="teamOptions"
                    placeholder="请选择"
                    allow-clear
                  />
                </a-form-item>
              </a-col>
            </a-row>
            <a-row v-if="moreSelectOption" :gutter="16">
              <a-col :span="4">
                <a-form-item field="status" label="未跟进时间">
                  <a-input v-model="formModel.notFollow" placeholder="请输入" />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="status" label="备注">
                  <a-input v-model="formModel.remark" placeholder="请输入" />
                </a-form-item>
              </a-col>
              <a-col :span="5">
                <a-form-item field="mobile" label="跟进次数">
                  <a-input-group>
                    <a-input v-model="formModel.minFt" placeholder="" /> -
                    <a-input v-model="formModel.maxFt" placeholder="" />
                  </a-input-group>
                </a-form-item>
              </a-col>
              <a-col v-if="route.name !== 'CustomerPool'" :span="5">
                <a-form-item field="mobile" label="申请时间">
                  <a-range-picker
                    v-model="formModel.createdTime"
                    style="width: 100%"
                    show-time
                    :time-picker-props="{
                      defaultValue: ['00:00:00', '23:59:59'],
                    }"
                  />
                </a-form-item>
              </a-col>
              <a-col v-if="route.name !== 'CustomerPool'" :span="5">
                <a-form-item field="status" label="跟进时间">
                  <a-range-picker
                    v-model="formModel.followTime"
                    style="width: 100%"
                    show-time
                    :time-picker-props="{
                      defaultValue: ['00:00:00', '23:59:59'],
                    }"
                  />
                </a-form-item>
              </a-col>
              <a-col v-if="route.name === 'CustomerPool'" :span="5">
                <a-form-item field="mobile" label="申请时间">
                  <a-range-picker
                    v-model="formModel.createdTime"
                    style="width: 100%"
                    show-time
                    :time-picker-props="{
                      defaultValue: ['00:00:00', '23:59:59'],
                    }"
                  />
                </a-form-item>
              </a-col>
              <a-col v-if="route.name === 'CustomerPool'" :span="5">
                <a-form-item field="status" label="跟进时间">
                  <a-range-picker
                    v-model="formModel.followTime"
                    style="width: 100%"
                    show-time
                    :time-picker-props="{
                      defaultValue: ['00:00:00', '23:59:59'],
                    }"
                  />
                </a-form-item>
              </a-col>
              <a-col v-if="route.name === 'CustomerPool'" :span="5">
                <a-form-item field="status" label="公海时间">
                  <a-range-picker
                    v-model="formModel.giveupTime"
                    style="width: 100%"
                    show-time
                    :time-picker-props="{
                      defaultValue: ['00:00:00', '23:59:59'],
                    }"
                  />
                </a-form-item>
              </a-col>
            </a-row>
          </a-form>
        </a-col>
        <a-divider style="height: 38px" direction="vertical" />
        <a-col :flex="'25px'" style="text-align: center">
          <a-space>
            <a v-if="!moreSelectOption" @click="moreSelectOption = true">
              <icon-down />
            </a>
            <a v-if="moreSelectOption" @click="moreSelectOption = false">
              <icon-up />
            </a>
          </a-space>
        </a-col>
      </a-row>
      <a-divider style="margin-top: 0" />
      <a-row style="margin-bottom: 5px">
        <a-col :span="16">
          <a-space>
            <a-button type="primary" @click="search">
              <template #icon>
                <icon-search />
              </template>
              查询
            </a-button>
            <a-button
              v-if="
                (userStore.$state.buttonPermission.includes('CustomerExport') && route.name === 'CustomerList') || 
                (userStore.$state.buttonPermission.includes('CustomerExport') && route.name === 'MyCustomerList') || 
                (userStore.$state.buttonPermission.includes( 'CustomerImportExport') && route.name === 'CustomerImportList') ||
                (userStore.$state.buttonPermission.includes( 'CustomerInnerExport') && route.name === 'CustomerInnerList') ||
                (userStore.$state.buttonPermission.includes( 'CustomerNewExport') && route.name === 'CustomerNewList') ||
                (userStore.$state.buttonPermission.includes( 'CustomerPoolExport') && route.name === 'CustomerPool') ||
                (userStore.$state.buttonPermission.includes( 'CustomerNewpoolExport') && route.name === 'CustomerNewpool') ||
                (userStore.$state.buttonPermission.includes( 'CustomerUnvalidExport') && route.name === 'CustomerUnvalid')
              "
              type="primary"
              status="success"
              @click="download"
            >
              <template #icon> <icon-download /> </template> 导出
            </a-button>
            <a-button
              v-if="route.name == 'CustomerList' || route.name == 'MyCustomerList'"
              v-permission="['CustomerEdit']"
              status="success"
              type="primary"
              @click="add()"
            >
              <template #icon>
                <icon-plus />
              </template>
              录入客户
            </a-button>
            <a-button
              v-if="
                (userStore.$state.buttonPermission.includes('CustomerAssign') && route.name === 'CustomerList') ||
                (userStore.$state.buttonPermission.includes('CustomerAssign') && route.name === 'MyCustomerList') ||
                (userStore.$state.buttonPermission.includes('CustomerImportAssign') && route.name === 'CustomerImportList') ||
                (userStore.$state.buttonPermission.includes('CustomerInnerAssign') && route.name === 'CustomerInnerList') ||
                (userStore.$state.buttonPermission.includes('CustomerNewAssign') && route.name === 'CustomerNewList') ||
                (userStore.$state.buttonPermission.includes( 'CustomerAssignPool') && route.name === 'CustomerPool') ||
                (userStore.$state.buttonPermission.includes( 'CustomerAssignNewPool') && route.name === 'CustomerNewpool') ||
                (userStore.$state.buttonPermission.includes( 'CustomerAssignUnvalid') && route.name === 'CustomerUnvalid')
              "
              status="success"
              type="primary"
              @click="showAssignModal()"
            >
              <template #icon>
                <icon-branch />
              </template>
              分配
            </a-button>
            <a-button
              v-if="
                (userStore.$state.buttonPermission.includes( 'CustomerBatchUploadPool') && route.name === 'CustomerPool') ||
                (userStore.$state.buttonPermission.includes( 'CustomerBatchUploadNewPool') && route.name === 'CustomerNewpool')
              "
              status="success"
              type="primary"
              @click="uploadModal = true"
            >
              <template #icon>
                <icon-upload />
              </template>
              批量导入
            </a-button>
            <a-popconfirm
              v-if="
                (userStore.$state.buttonPermission.includes( 'BatchCustomerGiveup') && route.name == 'CustomerList') ||
                (userStore.$state.buttonPermission.includes( 'BatchCustomerGiveup') && route.name == 'MyCustomerList') ||
                (userStore.$state.buttonPermission.includes( 'BatchCustomerInnerGiveup') && route.name == 'CustomerInnerList') 
              "
              content="确认批量移入公海吗?"
              ok-text="确认"
              cancel-text="取消"
              @ok="batchGiveup()"
            >
              <a-button status="success" type="primary">
                <template #icon>
                  <icon-eraser />
                </template>
                移入公海
              </a-button>
            </a-popconfirm>
            <a-popconfirm
              v-if="
                (userStore.$state.buttonPermission.includes(
                  'BatchCustomerGetPool'
                ) &&
                  route.name === 'CustomerPool') ||
                (userStore.$state.buttonPermission.includes(
                  'BatchCustomerGetNewPool'
                ) &&
                  route.name === 'CustomerNewpool')
              "
              content="确定批量认领客户吗?"
              ok-text="确认"
              cancel-text="取消"
              @ok="batchGet()"
            >
              <a-button status="success" type="primary">
                <template #icon>
                  <icon-export />
                </template>
                批量认领
              </a-button>
            </a-popconfirm>
          </a-space>
        </a-col>
      </a-row>
      <a-space
        v-if="route.name === 'CustomerList' || route.name === 'CustomerNewList'"
        :size="1"
        style="margin-bottom: 10px"
      >
        <a-typography-text type="warning">注:</a-typography-text>
        <a-typography-text type="success"
          ><a-link @click="quickSearch('1')">{{
            data1.num1
          }}</a-link></a-typography-text
        >
        <a-typography-text>位0星超1天未跟进，</a-typography-text>
        <a-typography-text type="success"
          ><a-link @click="quickSearch('6')">{{
            data1.num2
          }}</a-link></a-typography-text
        >
        <a-typography-text>位2星3星超过6天未跟进，</a-typography-text>
        <a-typography-text type="success"
          ><a-link @click="quickSearch('3')">{{
            data1.num3
          }}</a-link></a-typography-text
        >
        <a-typography-text>位4星5星超过3天未跟进</a-typography-text>
        <a-typography-text>。请注意及时更新</a-typography-text>
        <a-link @click="reflushData1()">刷新</a-link>
      </a-space>
      <a-table
        v-model:selectedKeys="selectedKeys"
        row-key="id"
        :row-selection="rowSelection"
        :scroll="scrollPercent"
        :loading="loading"
        :pagination="false"
        :data="renderData"
        :bordered="false"
        label-align="center"
        :hoverable="false"
        @page-change="onPageChange"
      >
        <template #tr="{ record }">
          <tr :class="record.is_follow === 0 ? 'notfollow' : ''" />
        </template>
        <template #columns>
          <a-table-column
            title="id"
            data-index="id"
            :width="80"
            :body-cell-style="tableStyle"
          >
            <template #cell="{ record }">
            <span
                v-if="
                  !userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) || record.follow_user_id == 0
                "
              >
                {{ record.id}}
              </span>
              <a-link
                v-if="
                  userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) && record.follow_user_id != 0
                "
                @click="follow(record.id)"
              >
                {{ record.id}}
              </a-link>
            </template>
          </a-table-column>
          <a-table-column
            title="姓名"
            data-index="name"
            :width="100"
            :body-cell-style="tableStyle"
          >
            <template #cell="{ record }">
              <icon-lock v-if="record.lock == 1" style="margin-right: 5px" />
              <span
                v-if="
                  !userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) || record.follow_user_id == 0
                "
              >
                {{ record.name }}
              </span>
              <a-link
                v-if="
                  userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) && record.follow_user_id != 0
                "
                @click="follow(record.id)"
              >
                {{ record.name }}
              </a-link>
              <a-tag v-if="record.anum > 0" color="red"
                >+{{ record.anum }}</a-tag
              >
            </template>
          </a-table-column>
          <a-table-column
            title="跟进状态"
            data-index="follow_status"
            :width="100"
            :body-cell-style="tableStyle"
          >
            <template #cell="{ record }">
                <span
                    v-if="
                      !userStore.$state.buttonPermission.includes(
                        'CustomerFollow'
                      ) || record.follow_user_id == 0
                    "
                  >
                  <a-tag v-if="record.fstatuscolor" :color="record.fstatuscolor">{{
                    record.follow_status
                  }}</a-tag>
                  </span>
                  <a-link
                    v-if="
                      userStore.$state.buttonPermission.includes(
                        'CustomerFollow'
                      ) && record.follow_user_id != 0
                    "
                    @click="follow(record.id)"
                  >
                  <a-tag v-if="record.fstatuscolor" :color="record.fstatuscolor">{{
                    record.follow_status
                  }}</a-tag>
                  </a-link>
            </template>
          </a-table-column>
          <a-table-column
            title="年龄"
            data-index="age"
            :width="60"
            :body-cell-style="tableStyle"
          >
          <template #cell="{ record }">
            <span
                v-if="
                  !userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) || record.follow_user_id == 0
                "
              >
                {{ record.age}}
              </span>
              <a-link
                v-if="
                  userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) && record.follow_user_id != 0
                "
                @click="follow(record.id)"
              >
                {{ record.age}}
              </a-link>
            </template>
          </a-table-column>

          <a-table-column
            title="城市"
            data-index="city"
            :width="60"
            :body-cell-style="tableStyle"
          >
          <template #cell="{ record }">
            <span
                v-if="
                  !userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) || record.follow_user_id == 0
                "
              >
                {{ record.city}}
              </span>
              <a-link
                v-if="
                  userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) && record.follow_user_id != 0
                "
                @click="follow(record.id)"
              >
                {{ record.city}}
              </a-link>
            </template>
          </a-table-column>
          <a-table-column
            title="申请金额"
            data-index="amount"
            :width="90"
            :body-cell-style="tableStyle"
          >
          <template #cell="{ record }">
            <span
                v-if="
                  !userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) || record.follow_user_id == 0
                "
              >
                {{ record.amount}}
              </span>
              <a-link
                v-if="
                  userStore.$state.buttonPermission.includes(
                    'CustomerFollow'
                  ) && record.follow_user_id != 0
                "
                @click="follow(record.id)"
              >
                {{ record.amount}}
              </a-link>
            </template>
          </a-table-column>
          <a-table-column
            title="资质"
            data-index="zizhi"
            :width="160"
            :body-cell-style="tableStyle"
          />
          <a-table-column
            title="星级"
            :width="60"
            :body-cell-style="tableStyle"
          >
            <template #cell="{ record }">
              <a-tag v-if="record.starcolor" :color="record.starcolor">{{
                record.star
              }}</a-tag>
            </template>
          </a-table-column>
          <a-table-column
            title="跟进备注"
            data-index="remark"
            :width="200"
            :body-cell-style="tableStyle"
          >
            <template #cell="{ record }">
              <div :title="record.remark" v-html="record.subremark"></div>
            </template>
          </a-table-column>
          <a-table-column
            title="未跟进时间"
            data-index="notFollowTime"
            :width="145"
            :body-cell-style="tableStyle"
          >
            <template #cell="{ record }">
              {{ record.notFollowTime }}
            </template>
          </a-table-column>
          <a-table-column
            title="跟进/分配/申请时间"
            :width="180"
            :body-cell-style="tableStyle"
          >
            <template #cell="{ record }">
              {{ record.follow_time }} <br />
              {{ record.assign_time }} <br />
              {{ record.apply_time }}
            </template>
          </a-table-column>
          <a-table-column
            title="跟进顾问"
            data-index="follow_user"
            :width="90"
            :body-cell-style="tableStyle"
          />
          <a-table-column
            title="数据来源"
            data-index="source"
            :width="90"
            :body-cell-style="tableStyle"
          />
          <a-table-column
            title="客户来源"
            data-index="user_from"
            :width="90"
            :body-cell-style="tableStyle"
          />
          <a-table-column
            title="其他信息"
            data-index="qualification"
            :width="90"
            :body-cell-style="tableStyle"
          />
          <a-table-column
            title="操作"
            data-index="operations"
            fixed="right"
            :width="100"
            :body-cell-style="tableStyle"
          >
            <template #cell="{ record }">
              <a-button
                v-if="record.follow_user_id != 0"
                v-permission="['CustomerFollow']"
                type="text"
                size="small"
                @click="follow(record.id)"
              >
                <icon-shake /> 跟进
              </a-button>
              <a-popconfirm
                v-if="
                  (record.follow_user_id == 0 &&
                    userStore.$state.buttonPermission.includes(
                      'CustomerGetPool'
                    ) &&
                    route.name === 'CustomerPool') ||
                  (userStore.$state.buttonPermission.includes(
                    'CustomerGetNewPool'
                  ) &&
                    route.name === 'CustomerNewpool')
                "
                content="确定认领该客户么?"
                ok-text="确认"
                cancel-text="取消"
                @ok="get(record.id)"
              >
                <a-button type="text" size="small">
                  <icon-bookmark /> 认领
                </a-button>
              </a-popconfirm>
              <a-button
                v-if="
                  (record.follow_user_id == 0 &&
                    master == 1 &&
                    userStore.$state.buttonPermission.includes(
                      'CustomerEditPool'
                    ) &&
                    route.name === 'CustomerPool') ||
                  (userStore.$state.buttonPermission.includes(
                    'CustomerEditNewPool'
                  ) &&
                    route.name === 'CustomerNewpool')
                "
                type="text"
                size="small"
                @click="edit(record.id)"
              >
                <icon-edit /> 编辑
              </a-button>
              <a-button
                v-if="
                  (((record.follow_user_id == 0 && master == 1) ||
                    record.follow_user_id > 0) &&
                    userStore.$state.buttonPermission.includes(
                      'CustomerPreviewPool'
                    ) &&
                    route.name === 'CustomerPool') ||
                  (userStore.$state.buttonPermission.includes(
                    'CustomerPreview'
                  ) &&
                    (route.name === 'CustomerUnvalid' ||
                      route.name === 'CustomerList' ||
                      route.name === 'CustomerNewList' ||
                      route.name === 'CustomerInnerList' ||
                      route.name === 'CustomerImportList')) ||
                  (userStore.$state.buttonPermission.includes(
                    'CustomerPreviewNewPool'
                  ) &&
                    route.name === 'CustomerNewpool')
                "
                type="text"
                size="small"
                @click="
                  $router.push({
                    name: 'CustomerPreview',
                    params: { id: record.id },
                  })
                "
              >
                <icon-eye /> 查看
              </a-button>
            </template>
          </a-table-column>
        </template>
      </a-table>
      <a-pagination
        v-if="pagination.total > 0"
        show-page-size
        style="justify-content: flex-end; margin-top: 20px"
        :total="pagination.total"
        :current="pagination.current"
        :default-page-size="pagination.pageSize"
        show-jumper
        show-total
        :page-size-options="[10, 20, 50, 100, 150, 200, 500, 1000, 2000]"
        @change="onPageChange"
        @page-size-change="setPageSizeAndGo"
      />
    </a-card>
    <a-modal
      v-model:visible="assignModal"
      title="分配客户"
      :footer="false"
    >
      <a-select
        v-model="followUserId"
        :options="followUserOptions"
        placeholder="请选择"
        allow-clear
        :multiple="true"
      />
      <br />
      <br />
      <center>
      <a-button type="primary" @click="assign()">确 认</a-button>
      </center>
    </a-modal>
    <a-modal
      v-model:visible="uploadModal"
      title="批量导入"
      @before-close="colseModal()"
    >
      <template #footer>&nbsp;</template>
      <a-link target="_blank" href="/resource/template.xlsx">下载模板</a-link>
      <a-select v-model="uploadqudao" :options="sourceOptions" placeholder="请选择渠道" allow-clear />
      <a-upload draggable :action="uploadaction" :onSuccess="uploadOver"
        ref="uploadRef"
        v-model:file-list="files"
        :on-success="uploadOver"
        :on-button-click="resetUploadMessage"
        :name="fileName"
        :limit="1"
        :auto-upload="false"
      />
      <a-alert
        v-if="uploadMessage != ''"
        :type="alertType"
        style="margin-top: 10px"
      >
        <pre>{{ uploadMessage }}</pre>
      </a-alert>
    </a-modal>
  </div>
</template>

<script lang="ts" setup>
  import { ref, reactive, toRef, toRefs,computed } from 'vue';
  import useLoading from '@/hooks/loading';
  import {
    queryCustomList,
    CustomInfo,
    CustomListParams,
    assignCustomInfo,
    laheiCustom,
    customerGet,
    CustomBatchGiveup,
    CustomBatchGet,
    getFormModel,
    initFormModel,
    getPage,
    setPage,
    getPageSize,
    setPageSize,
    queryCustomData,
  } from '@/api/custom';
  import { Pagination } from '@/types/global';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import type { TableRowSelection } from '@arco-design/web-vue/es/table/interface';
  import { Message } from '@arco-design/web-vue';
  import { onBeforeRouteLeave, useRoute, useRouter } from 'vue-router';
  import { useAppStore } from '@/store';
  import { setNum } from '@/api/customernum';

  const userStore = useAppStore();
  const route = useRoute();
  const router = useRouter();
  const uploadRef = ref();
  const uploadMessage = ref('');
  const files = ref([]);
  const uploadqudao = ref('');
const uploadaction = computed(function() {
  return `/api/custom/upload?source=${uploadqudao.value}`
})
  const fileName = ref(route.name as string);
  const alertType = ref<'error' | 'info' | 'success' | 'warning' | undefined>(
    'success'
  );
  const crumb = ref();
  if (route.name === 'CustomerPool') {
    crumb.value = '公共池客户';
  } else if (route.name === 'CustomerNewpool') {
    crumb.value = '新数据公共池';
  } else if (route.name === 'CustomerImportList') {
    crumb.value = '重要客户';
  } else if (route.name === 'CustomerInnerList') {
    crumb.value = '再分配客户';
  } else if (route.name === 'CustomerNewList') {
    crumb.value = '新客户';
  } else if (route.name === 'CustomerUnvalid') {
    crumb.value = '无效客户';
  } else if (route.name === 'MyCustomerList') {
    crumb.value = '我的客户';
  } else {
    crumb.value = '全部客户';
  }
  const scrollPercent = { y: '100%' };
  const rowSelection = ref<TableRowSelection>({
    type: 'checkbox',
    showCheckedAll: true,
  });

  const tableStyle = (record: any) => {
    const returnData = ref({ 'color': '', 'background-color': '' });
    if (record.important === 1) {
      returnData.value.color = 'rgb(var(--orange-6))';
    }
    return returnData.value;
  };

  const moreSelectOption = ref(true);
  const cityOptions = ref<SelectOptionData[]>([]);
  const followUserOptions = ref<SelectOptionData[]>([]);
  const followStatusOptions = ref<SelectOptionData[]>([]);
  const starOptions = ref<SelectOptionData[]>([]);
  const sourceOptions = ref<SelectOptionData[]>([]);
  const userFromOptions = ref<SelectOptionData[]>([]);
  const zizhiOptions = ref<SelectOptionData[]>([]);
  const teamOptions = ref<SelectOptionData[]>([]);

  const selectedKeys = ref([]);
  const assignModal = ref(false);
  const uploadModal = ref(false);
  const followUserId = ref();
  const master = ref();
  const data1 = ref({ num1: '', num2: '', num3: '', num4: '', num5: '' });
  const customIds = ref();

  const { loading, setLoading } = useLoading(true);
  const renderData = ref<CustomInfo[]>([]);
  const formModel = ref(getFormModel());
  const basePagination: Pagination = {
    current: getPage(),
    pageSize: getPageSize(),
  };
  const pagination = reactive({ ...basePagination, total: 0 });

  onBeforeRouteLeave((to, from, next) => {
    if (
      to.name !== 'CustomerEdit' &&
      to.name !== 'CustomerPreview' &&
      to.name !== 'CustomerFollow'
    ) {
      initFormModel();
      setPage(1);
      setPageSize(20);
    }
    next();
  });
  const showAssignModal = () => {
    if (selectedKeys.value.length === 0) {
      Message.error({
        content: '请选择要分配的客户',
        duration: 5 * 1000,
      });
      return;
    }
    assignModal.value = true;
  };

  const fetchData = async (
    params: CustomListParams = {
      current: 1,
      pageSize: 20,
      type: route.name as string,
    }
  ) => {
    setLoading(true);
    try {
      const { data } = await queryCustomList(params);
      renderData.value = data.list;
      cityOptions.value = data.cityList;
      followUserOptions.value = data.followUserList;
      followStatusOptions.value = data.followStatusList;
      teamOptions.value = data.teamList;
      starOptions.value = data.starList;
      sourceOptions.value = data.sourceList;
      userFromOptions.value = data.userFromList;
      master.value = data.master;
      zizhiOptions.value = data.zizhiList;
      pagination.current = params.current;
      pagination.total = data.total;
      customIds.value = data.customIds;
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };
  const onPageChange = (current: number) => {
    setPage(current);
    fetchData({
      ...basePagination,
      current,
      'type': route.name as string,
      'pageSize': getPageSize(),
      ...formModel.value,
      'zizhi[]': formModel.value.zizhi,
      'createTime[]': formModel.value.createdTime,
      'followTime[]': formModel.value.followTime,
      'giveupTime[]': formModel.value.giveupTime,
    } as unknown as CustomListParams);
  };

  const download = () => {
    const u = new URLSearchParams({
      current: '1',
      pageSize: '100000',
      type: route.name as string,
      ...formModel.value,
    } as unknown as string[][]).toString();
    const createTime = ref('');
    if (
      formModel.value.createdTime !== undefined &&
      formModel.value.createdTime.length === 2
    ) {
      createTime.value = `createTime[0]=${formModel.value.createdTime[0]}&createTime[1]=${formModel.value.createdTime[1]}`;
    }
    const followTime = ref('');
    if (
      formModel.value.followTime !== undefined &&
      formModel.value.followTime.length === 2
    ) {
      followTime.value = `followTime[0]=${formModel.value.followTime[0]}&followTime[1]=${formModel.value.followTime[1]}`;
    }
    (
      window as Window
    ).location = `/api/custom/list?export=1&${u}&${createTime.value}&${followTime.value}`;
  };
  const setPageSizeAndGo = (pageSize: number) => {
    setPageSize(pageSize);
    onPageChange(1);
  };
  const search = () => {
    onPageChange(1);
  };
  const resetUploadMessage = () => {
    uploadMessage.value = '';
  };
  const colseModal = () => {
    files.value = [];
    resetUploadMessage();
  };
  const uploadOver = (response: any) => {
    const returnData = response.response;
    files.value = [];
    uploadRef.value.fileList.pop();
    uploadRef.value.fileList.value = [];
    if (returnData.code === 20000) {
      alertType.value = 'success';
      uploadMessage.value = returnData.msg;
      search();
      colseModal();
      uploadModal.value = false;
      alert(returnData.msg);
    } else {
      alertType.value = 'error';
      uploadMessage.value = returnData.msg;
    }
    setTimeout(() => {
      files.value = [];
    }, 500);
  };

  onPageChange(getPage());

  const assign = async () => {
    if (followUserId.value === undefined || followUserId.value <= 0) {
      Message.error({
        content: '请选择跟进用户',
        duration: 5 * 1000,
      });
      return false;
    }

    try {
      await assignCustomInfo(followUserId.value, selectedKeys.value);
      selectedKeys.value = [];
      followUserId.value = '';
      search();
      assignModal.value=false;
    } catch (err) {
      //
    } finally {
      //
    }
    return true;
  };

  const batchGiveup = async () => {
    try {
      if (selectedKeys.value.length === 0) {
        Message.error({
          content: '请选择要移入公海的客户',
          duration: 5 * 1000,
        });
        return false;
      }
      await CustomBatchGiveup(selectedKeys.value);
      selectedKeys.value = [];
      search();
    } catch (err) {
      //
    } finally {
      //
    }
    return true;
  };

  const batchGet = async () => {
    try {
      if (selectedKeys.value.length === 0) {
        Message.error({
          content: '请选择要批量认领的客户',
          duration: 5 * 1000,
        });
        return false;
      }
      await CustomBatchGet(selectedKeys.value);
      selectedKeys.value = [];
      setNum();
      search();
    } catch (err) {
      //
    } finally {
      //
    }
    return true;
  };

  const get = async (id: string) => {
    try {
      await customerGet(id);
      search();
      setNum();
    } catch (err) {
      //
    } finally {
      //
    }
    return true;
  };

  const lahei = async (record: CustomInfo, status: string) => {
    try {
      await laheiCustom(record.id, status);
      record.status = status;
    } catch (err) {
      //
    } finally {
      setLoading(false);
    }
  };

  const follow = (id: any) => {
    const routeUrl = router.resolve({
      name: 'CustomerFollow',
      params: { id, allIds: customIds.value },
    });
    window.open(routeUrl.href, '_blank');
  };
  const edit = (id: any) => {
    const routeUrl = router.resolve({
      name: 'CustomerEdit',
      params: { id, allIds: customIds.value },
    });
    window.open(routeUrl.href, '_blank');
  };
  const add = () => {
    const routeUrl = router.resolve({
      name: 'CustomerEdit',
    });
    window.open(routeUrl.href, '_blank');
  };
  const quickSearch = (day: string) => {
    initFormModel();
    formModel.value = getFormModel();
    formModel.value.notFollow = day;
    if (day === '1') {
      formModel.value.star = '-1';
    }
    if (day === '6') {
      formModel.value.star = '9';
    }
    if (day === '3') {
      formModel.value.star = '10';
    }
    search();
  };
  const reflushData1 = async () => {
    data1.value = { num1: '', num2: '', num3: '', num4: '', num5: '' };
    const { data } = await queryCustomData({ type: route.name });
    data1.value = data;
  };
  reflushData1();
</script>

<style lang="less">
  .container1 {
    padding: 0 20px 20px 20px;
  }

  .notfollow .arco-table-td {
    background-color: rgb(var(--link-1));
  }
</style>

<style>
  .bigtable1 .arco-table .arco-table-cell {
    padding: 5px 10px;
  }

  .bigtable1 .arco-link,
  .container1.bigtable1 .arco-table .arco-table-th,
  .bigtable1 .arco-table .arco-table-td {
    font-size: 12px;
  }

  .container1.bigtable1 .arco-form-item {
    margin-bottom: 5px;
  }

  .container1.bigtable1 .arco-divider-horizontal {
    margin-top: 5px;
    margin-bottom: 5px;
  }
</style>
