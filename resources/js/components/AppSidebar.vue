<script setup lang="ts">
import { type NavItem } from '@/../types';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LampDesk, BookType, BookText, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

interface PageProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      roles: string[];
      permissions: string[];
    } | null;
  };
}

const page = usePage<PageProps>();

const hasPermission = (permission: string) => {
  return page.props.auth.user?.permissions.includes(permission) ?? false;
};

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        permission: null,
    },
    {
        title: 'Helpdesk',
        href: '/helpdesk',
        icon: LampDesk,
        permission: 'view-helpdesk',
    },
    {
        title: 'Admin Knowledge Base',
        href: '/admin/knowledge-base',
        icon: BookText,
        permission: 'view-helpdesk',
    },
    {
        title: 'Knowledge Base',
        href: '/knowledge-base',
        icon: BookType,
        permission: 'admin',
    },
];

const filteredMainNavItems = computed(() => {
  return mainNavItems.filter(item =>
    item.permission === null || hasPermission(item.permission)
  );
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <div class="flex items-center justify-between">
                        <SidebarMenuButton size="lg" as-child>
                            <Link :href="route('dashboard')">
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                        <SidebarTrigger class="flex items-center gap-2">
                        </SidebarTrigger>
                    </div>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
