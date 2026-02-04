<script lang="tsx">
  import { defineComponent, ref, h, compile, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter, RouteRecordRaw } from 'vue-router';
  import type { RouteMeta } from 'vue-router';
  import { useAppStore } from '@/store';
  import { listenerRouteChange } from '@/utils/route-listener';
  import { openWindow, regexUrl } from '@/utils';
  import { customNum, setNum, approveNum } from '@/api/customernum';
  import useMenuTree from './useMenuTree';

  export default defineComponent({
    emit: ['collapse'],
    setup() {
      const { t } = useI18n();
      const appStore = useAppStore();
      const router = useRouter();
      const route = useRoute();
      const { menuTree } = useMenuTree();
      const collapsed = computed({
        get() {
          if (appStore.device === 'desktop') return appStore.menuCollapse;
          return false;
        },
        set(value: boolean) {
          appStore.updateSettings({ menuCollapse: value });
        },
      });

      const openKeys = ref<string[]>([]);
      const selectedKey = ref<string[]>([]);

      setNum();
      setInterval(function () {
        setNum();
      }, 10000);
      const goto = (item: RouteRecordRaw) => {
        // Open external link
        if (regexUrl.test(item.path)) {
          openWindow(item.path);
          selectedKey.value = [item.name as string];
          return;
        }
        // Eliminate external link side effects
        const { hideInMenu, activeMenu } = item.meta as RouteMeta;
        if (route.name === item.name && !hideInMenu && !activeMenu) {
          selectedKey.value = [item.name as string];
          return;
        }
        // Trigger router change
        // 兼容处理：将 FinanceRepayment 修正为 FinancePayment
        let routeName = item.name as string;
        if (routeName === 'FinanceRepayment') {
          routeName = 'FinancePayment';
        }
        router.push({
          name: routeName,
        });
      };
      const findMenuOpenKeys = (name: string) => {
        const result: string[] = [];
        let isFind = false;
        const backtrack = (
          item: RouteRecordRaw,
          keys: string[],
          target: string
        ) => {
          if (item.name === target) {
            isFind = true;
            result.push(...keys, item.name as string);
            return;
          }
          if (item.children?.length) {
            item.children.forEach((el) => {
              backtrack(el, [...keys], target);
            });
          }
        };
        menuTree.value.forEach((el: RouteRecordRaw) => {
          if (isFind) return; // Performance optimization
          backtrack(el, [el.name as string], name);
        });
        return result;
      };
      listenerRouteChange((newRoute) => {
        const { requiresAuth, activeMenu, hideInMenu } = newRoute.meta;
        if (!hideInMenu || activeMenu) {
          const menuOpenKeys = findMenuOpenKeys(
            (activeMenu || newRoute.name) as string
          );

          const keySet = new Set([...menuOpenKeys, ...openKeys.value]);
          openKeys.value = [...keySet];

          selectedKey.value = [
            activeMenu || menuOpenKeys[menuOpenKeys.length - 1],
          ];
        }
      }, true);
      const setCollapse = (val: boolean) => {
        if (appStore.device === 'desktop')
          appStore.updateSettings({ menuCollapse: val });
      };

      const renderSubMenu = () => {
        function travel(_route: RouteRecordRaw[], nodes = []) {
          if (_route) {
            _route.forEach((element) => {
              // This is demo, modify nodes as needed
              const icon = element?.meta?.icon
                ? () => h(compile(`<${element?.meta?.icon}/>`))
                : null;
              const cnt = ref();
              if (element?.name === 'CustomerNewList' && customNum.value > 0) {
                cnt.value = h(
                  compile(
                    `<span style="padding: 0px; font-size: 12px; line-height: 20px; height: 20px; background: red; border-radius: 10px; color: #fff; margin-left: 10px; min-width: 20px; display: inline-block; text-align: center; font-weight: bold;">${
                      customNum.value > 99 ? '99+' : customNum.value
                    }</span>`
                  )
                );
              } else if (
                element?.name === 'ApproveOther' &&
                approveNum.value > 0
              ) {
                cnt.value = h(
                  compile(
                    `<span style="padding: 0px; font-size: 12px; line-height: 20px; height: 20px; background: red; border-radius: 10px; color: #fff; margin-left: 10px; min-width: 20px; display: inline-block; text-align: center; font-weight: bold;">${
                      approveNum.value > 99 ? '99+' : approveNum.value
                    }</span>`
                  )
                );
              } else {
                cnt.value = null;
              }
              const node =
                element?.children && element?.children.length !== 0 ? (
                  <a-sub-menu
                    key={element?.name}
                    v-slots={{
                      icon,
                      title: () => h(compile(t(element?.meta?.locale || ''))),
                    }}
                  >
                    {travel(element?.children)}
                  </a-sub-menu>
                ) : (
                  <a-menu-item
                    key={element?.name}
                    v-slots={{ icon }}
                    onClick={() => goto(element)}
                  >
                    {t(element?.meta?.locale || '')}
                    {cnt.value}
                  </a-menu-item>
                );
              nodes.push(node as never);
            });
          }
          return nodes;
        }
        return travel(menuTree.value);
      };

      return () => (
        <a-menu
          v-model:collapsed={collapsed.value}
          theme="dark"
          show-collapse-button={appStore.device !== 'mobile'}
          auto-open={true}
          selected-keys={selectedKey.value}
          auto-open-selected={true}
          level-indent={34}
          style="height: 100%"
          onCollapse={setCollapse}
        >
          {renderSubMenu()}
        </a-menu>
      );
    },
  });
</script>

<style lang="less" scoped>
  :deep(.arco-menu-inner) {
    .arco-menu-inline-header {
      display: flex;
      align-items: center;
    }

    .arco-icon {
      &:not(.arco-icon-down) {
        font-size: 18px;
      }
    }
  }
</style>
