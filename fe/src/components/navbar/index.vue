<template>
  <div class="navbar">
    <div class="left-side">
      <a-space>
        <a-typography-title
          :style="{ margin: 0, fontSize: '18px' }"
          :heading="5"
        >
        沪汇融-{{userStore.name}}
        </a-typography-title>
        <Paomadeng :gonggao="userStore.gonggao"/>
        <icon-menu-fold
          v-if="appStore.device === 'mobile'"
          style="font-size: 22px; cursor: pointer"
        />
      </a-space>
    </div>
    <ul class="right-side">
      <li>
        <a-switch v-model="online" @change="changeOnline" :disabled="true">
          <template #checked>
            在线
          </template>
          <template #unchecked>
            离线
          </template>
        </a-switch>
      </li>
      <li>
        <a-tooltip :content="$t('settings.navbar.alerts')">
          <div class="message-box-trigger">
            <a-badge :count="noticeCount">
              <a-button
                class="nav-btn"
                type="outline"
                :shape="'circle'"
                @click="setPopoverVisible"
              >
                <icon-notification />
              </a-button>
            </a-badge>
          </div>
        </a-tooltip>
        <a-popover
          trigger="click"
          :arrow-style="{ display: 'none' }"
          :content-style="{ padding: 0, minWidth: '440px' }"
          content-class="message-popover"
        >
          <div ref="refBtn" class="ref-btn"></div>
          <template #content>
            <message-box />
          </template>
        </a-popover>
      </li>
      <li>
        <a-tooltip
          :content="
            theme === 'light'
              ? $t('settings.navbar.theme.toDark')
              : $t('settings.navbar.theme.toLight')
          "
        >
          <a-button
            class="nav-btn"
            type="outline"
            :shape="'circle'"
            @click="handleToggleTheme"
          >
            <template #icon>
              <icon-moon-fill v-if="theme === 'dark'" />
              <icon-sun-fill v-else />
            </template>
          </a-button>
        </a-tooltip>
      </li>
      <li>
        <a-popover >
          <a-button class="nav-btn" type="outline" :shape="'circle'" >
            <icon-bookmark />
          </a-button>
          <template #content>
            来访签到二维码<br/>
            <a-image
              src="data:image/png;base64,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"
            />
          </template>
        </a-popover>
      </li>

      <li>
        <a-tooltip
          :content="
            isFullscreen
              ? $t('settings.navbar.screen.toExit')
              : $t('settings.navbar.screen.toFull')
          "
        >
          <a-button
            class="nav-btn"
            type="outline"
            :shape="'circle'"
            @click="toggleFullScreen"
          >
            <template #icon>
              <icon-fullscreen-exit v-if="isFullscreen" />
              <icon-fullscreen v-else />
            </template>
          </a-button>
        </a-tooltip>
      </li>
      <li>
        <a-dropdown trigger="click">
          <a-avatar
            :size="32"
            :style="{ marginRight: '8px', cursor: 'pointer' }"
          >
            <img alt="avatar" :src="avatar" />
          </a-avatar>
          <template #content>
            <a-doption>
                <div style="text-align: center;">
                  你好, {{userStore.name}}！
                </div>
            </a-doption>
            <a-divider style="margin:4px 0px"></a-divider>
            <a-doption>
              <a-space @click="$router.push({ name: 'Usersetting' })">
                <icon-settings />
                <span>
                  {{ $t('messageBox.userSettings') }}
                </span>
              </a-space>
            </a-doption>
            <a-doption>
              <a-space @click="handleLogout">
                <icon-export />
                <span>
                  {{ $t('messageBox.logout') }}
                </span>
              </a-space>
            </a-doption>
          </template>
        </a-dropdown>
      </li>
    </ul>
  </div>
</template>

<script lang="ts" setup>
  import { computed, ref, inject } from 'vue';
  import { Message } from '@arco-design/web-vue';
  import { useDark, useToggle, useFullscreen } from '@vueuse/core';
  import { useAppStore, useUserStore } from '@/store';
  import { LOCALE_OPTIONS } from '@/locale';
  import useLocale from '@/hooks/locale';
  import useUser from '@/hooks/user';
  import MessageBox from '../message-box/index.vue';
  import Paomadeng from './paomadeng.vue';

  const appStore = useAppStore();
  const userStore = useUserStore();
  const { logout } = useUser();
  const { changeLocale } = useLocale();
  const { isFullscreen, toggle: toggleFullScreen } = useFullscreen();
  const locales = [...LOCALE_OPTIONS];
  const avatar = computed(() => {
    return userStore.avatar;
  });
  const online = ref(userStore.online);
  const noticeCount = computed(() => {
    return userStore.noticeCount;
  });
  const theme = computed(() => {
    return appStore.theme;
  });
  const isDark = useDark({
    selector: 'body',
    attribute: 'arco-theme',
    valueDark: 'dark',
    valueLight: 'light',
    storageKey: 'arco-theme',
    onChanged(dark: boolean) {
      // overridden default behavior
      appStore.toggleTheme(dark);
    },
  });
  const toggleTheme = useToggle(isDark);
  const handleToggleTheme = () => {
    toggleTheme();
  };
  const setVisible = () => {
    appStore.updateSettings({ globalSettings: true });
  };
  const refBtn = ref();
  const triggerBtn = ref();
  const setPopoverVisible = () => {
    const event = new MouseEvent('click', {
      view: window,
      bubbles: true,
      cancelable: true,
    });
    refBtn.value.dispatchEvent(event);
  };
  const handleLogout = () => {
    logout();
  };
  const setDropDownVisible = () => {
    const event = new MouseEvent('click', {
      view: window,
      bubbles: true,
      cancelable: true,
    });
    triggerBtn.value.dispatchEvent(event);
  };
  const switchRoles = async () => {
    const res = await userStore.switchRoles();
    Message.success(res as string);
  };
  const changeOnline = async () => {
    const res = await userStore.switchOnlineProxy(online.value);
  };
  const toggleDrawerMenu = inject('toggleDrawerMenu');
</script>

<style scoped lang="less">
  .navbar {
    display: flex;
    justify-content: space-between;
    height: 100%;
    background-color: var(--color-bg-2);
    border-bottom: 1px solid var(--color-border);
  }

  .left-side {
    display: flex;
    align-items: center;
    padding-left: 20px;
  }

  .right-side {
    display: flex;
    padding-right: 20px;
    list-style: none;

    :deep(.locale-select) {
      border-radius: 20px;
    }

    li {
      display: flex;
      align-items: center;
      padding: 0 10px;
    }

    a {
      color: var(--color-text-1);
      text-decoration: none;
    }

    .nav-btn {
      color: rgb(var(--gray-8));
      font-size: 16px;
      border-color: rgb(var(--gray-2));
    }

    .trigger-btn,
    .ref-btn {
      position: absolute;
      bottom: 14px;
    }

    .trigger-btn {
      margin-left: 14px;
    }
  }
</style>

<style lang="less">
  .message-popover {
    .arco-popover-content {
      margin-top: 0;
    }
  }
</style>
