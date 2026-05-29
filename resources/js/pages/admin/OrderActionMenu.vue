<template>
    <div class="oam-wrap" ref="wrap">

        <!-- PRIMARY ACTION -->
        <button
            v-if="primaryAction"
            @click.stop="primaryAction.handler"
            :class="['oam-primary', `oam-primary--${primaryAction.variant || 'default'}`]"
            :title="primaryAction.label"
        >
            <span class="oam-primary__icon" v-html="primaryAction.icon" />
            <span class="oam-primary__label">{{ primaryAction.label }}</span>
        </button>

        <!-- OVERFLOW TRIGGER -->
        <button
            @click.stop="toggleMenu"
            :class="['oam-trigger', open ? 'oam-trigger--active' : '']"
            title="Aksi lainnya"
            ref="trigger"
        >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
            </svg>
        </button>

        <!-- DROPDOWN — Teleport ke body agar bebas dari overflow parent manapun -->
        <Teleport to="body">
            <Transition name="oam-drop">
                <div
                    v-if="open"
                    class="oam-menu"
                    :style="menuStyle"
                    ref="menu"
                    role="menu"
                >
                    <template v-for="(group, gi) in menuGroups" :key="gi">
                        <div v-if="gi > 0" class="oam-divider" />
                        <template v-for="item in group" :key="item.key">
                            <div v-if="item.sectionLabel" class="oam-section-label">{{ item.sectionLabel }}</div>
                            <button
                                @click.stop="handleMenuClick(item)"
                                :class="['oam-item', item.destructive ? 'oam-item--destructive' : '', item.disabled ? 'oam-item--disabled' : '']"
                                :disabled="item.disabled"
                                role="menuitem"
                            >
                                <span class="oam-item__icon" v-html="item.icon" />
                                <span class="oam-item__body">
                                    <span class="oam-item__label">{{ item.label }}</span>
                                    <span v-if="item.desc" class="oam-item__desc">{{ item.desc }}</span>
                                </span>
                                <span v-if="item.badge" class="oam-item__badge">{{ item.badge }}</span>
                            </button>
                        </template>
                    </template>
                </div>
            </Transition>
        </Teleport>

    </div>
</template>

<script>
const MENU_WIDTH  = 216
const MENU_OFFSET = 6

export default {
    name: 'OrderActionMenu',

    props: {
        primaryAction: { type: Object, default: null },
        menuGroups:    { type: Array,  default: () => [] },
        menuPosition:  { type: String, default: 'left' },
    },

    data() {
        return {
            open:     false,
            menuTop:  0,
            menuLeft: 0,
        }
    },

    computed: {
        menuStyle() {
            return {
                position: 'fixed',
                top:      this.menuTop  + 'px',
                left:     this.menuLeft + 'px',
                width:    MENU_WIDTH    + 'px',
                zIndex:   9999,
            }
        }
    },

    mounted() {
        document.addEventListener('click',   this.onOutsideClick)
        document.addEventListener('keydown', this.onKeydown)
        document.addEventListener('scroll',  this.onScroll, true)
        window.addEventListener('resize',    this.closeMenu)
    },

    beforeUnmount() {
        document.removeEventListener('click',   this.onOutsideClick)
        document.removeEventListener('keydown', this.onKeydown)
        document.removeEventListener('scroll',  this.onScroll, true)
        window.removeEventListener('resize',    this.closeMenu)
    },

    methods: {
        toggleMenu() {
            if (this.open) {
                this.closeMenu()
            } else {
                this.calcPosition()
                this.open = true
            }
        },

        closeMenu() {
            this.open = false
        },

        calcPosition() {
            const trigger = this.$refs.trigger
            if (!trigger) return

            const rect = trigger.getBoundingClientRect()

            // Hitung top — default di bawah trigger
            const topBelow = rect.bottom + MENU_OFFSET

            // Estimasi tinggi menu dari jumlah item
            const itemCount          = this.menuGroups.flat().length
            const estimatedMenuHeight = itemCount * 40 + 24

            // Flip ke atas kalau tidak cukup ruang di bawah
            const finalTop = (topBelow + estimatedMenuHeight > window.innerHeight - 8)
                ? rect.top - estimatedMenuHeight - MENU_OFFSET
                : topBelow

            // Hitung left — rata kanan trigger secara default
            let finalLeft = rect.right - MENU_WIDTH

            // Kalau mepet kiri viewport, geser ke kanan mulai dari trigger
            if (finalLeft < 8) finalLeft = rect.left

            this.menuTop  = finalTop
            this.menuLeft = finalLeft
        },

        onOutsideClick(e) {
            const wrap = this.$refs.wrap
            const menu = this.$refs.menu
            if (
                (wrap && !wrap.contains(e.target)) &&
                (menu && !menu.contains(e.target))
            ) {
                this.closeMenu()
            }
        },

        onKeydown(e) {
            if (e.key === 'Escape') this.closeMenu()
        },

        onScroll() {
            if (this.open) this.closeMenu()
        },

        handleMenuClick(item) {
            if (item.disabled) return
            this.closeMenu()
            item.handler?.()
        },
    }
}
</script>

<style scoped>
.oam-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* ── PRIMARY BUTTON ── */
.oam-primary {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 6px;
    border: 1px solid transparent;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all .15s;
    line-height: 1;
}
.oam-primary--default         { background: #f3f4f6; border-color: #e5e7eb; color: #374151; }
.oam-primary--default:hover   { background: #e5e7eb; color: #111827; }
.oam-primary--success         { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.oam-primary--success:hover   { background: #dcfce7; border-color: #86efac; }
.oam-primary--warning         { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.oam-primary--warning:hover   { background: #fef3c7; }
.oam-primary--blue            { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.oam-primary--blue:hover      { background: #dbeafe; }

.oam-primary__icon { display: flex; align-items: center; flex-shrink: 0; }
.oam-primary__icon :deep(svg) { display: block; }
.oam-primary__label { line-height: 1; }

/* ── TRIGGER ── */
.oam-trigger {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #9ca3af;
    cursor: pointer;
    transition: all .15s;
    flex-shrink: 0;
}
.oam-trigger:hover,
.oam-trigger--active {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #374151;
}
</style>

<!-- Style global untuk elemen yang di-teleport ke body -->
<style>
.oam-menu {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,.12), 0 2px 8px rgba(0,0,0,.06);
    padding: 4px;
    box-sizing: border-box;
}

.oam-divider {
    height: 1px;
    background: #f3f4f6;
    margin: 3px 4px;
}

.oam-section-label {
    padding: 6px 10px 3px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #9ca3af;
}

.oam-item {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    padding: 7px 10px;
    border-radius: 7px;
    border: none;
    background: transparent;
    color: #374151;
    cursor: pointer;
    text-align: left;
    transition: background .12s;
    font-size: 13px;
    line-height: 1;
    box-sizing: border-box;
}
.oam-item:hover                   { background: #f9fafb; }
.oam-item--destructive            { color: #dc2626; }
.oam-item--destructive:hover      { background: #fef2f2; }
.oam-item--disabled               { opacity: .4; cursor: not-allowed; }
.oam-item--disabled:hover         { background: transparent; }

.oam-item__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #9ca3af;
    width: 16px;
}
.oam-item--destructive .oam-item__icon { color: #fca5a5; }
.oam-item__icon svg { display: block; }

.oam-item__body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
    min-width: 0;
}
.oam-item__label { font-size: 13px; font-weight: 500; line-height: 1.2; }
.oam-item__desc  { font-size: 11px; color: #9ca3af; line-height: 1.3; }
.oam-item--destructive .oam-item__desc { color: #fca5a5; }

.oam-item__badge {
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 20px;
    background: #ede9fe;
    color: #7c3aed;
    flex-shrink: 0;
}

/* Transition */
.oam-drop-enter-active { transition: all .15s cubic-bezier(.16,1,.3,1); }
.oam-drop-leave-active { transition: all .1s ease; }
.oam-drop-enter-from   { opacity: 0; transform: translateY(-6px) scale(.97); }
.oam-drop-leave-to     { opacity: 0; transform: translateY(-4px) scale(.98); }
</style>