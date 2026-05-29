import { computed, isRef } from 'vue'
import { useHead } from '@vueuse/head'

/**
 * Composable SEO reusable untuk semua halaman
 *
 * @param {Object} opts
 * @param {string|Ref<string>} opts.title
 * @param {string|Ref<string>} opts.description
 * @param {string|Ref<string>} [opts.image]
 * @param {'website'|'product'|'article'} [opts.type='website']
 * @param {Object|Ref<Object>} [opts.jsonLd]
 */

export function useSeoMeta({ title, description, image, type = 'website', jsonLd } = {}) {
    const _title    = isRef(title)       ? title        : computed(() => title || 'TB Store')
    const _desc     = isRef(description) ? description  : computed(() => description || '')
    const _image    = isRef(image)       ? image        : computed(() => image || '/images/og-default.jpg')
    const _url      = computed(() => typeof window !== 'undefined'
        ? window.location.origin + window.location.pathname : '/')

    useHead({
        title: computed(() => `${_title.value} | TB Store`),
        meta: [
            { name: 'description',          content: _desc },
            { name: 'robots',               content: 'index, follow' },
            { property: 'og:type',          content: type },
            { property: 'og:url',           content: _url },
            { property: 'og:title',         content: _title },
            { property: 'og:image',         content: _image },
            { property: 'og:locale',        content: 'id_ID' },
            { name: 'twitter:card',         content: 'summary_large_image' },
            { name: 'twitter:title',        content: _title },
            { name: 'twitter:description',  content: _desc },
            { name: 'twitter:image',        content: _image },
        ],
        link: [
            { rel: 'canonical', href: _url },
        ],
        ...(jsonLd && {
            script: [{
                type: 'application/ld+json',
                innerHTML: isRef(jsonLd)
                    ? computed(() => JSON.stringify(jsonLd.value))
                    : computed(() => JSON.stringify(jsonLd)),
            }],
        }),
    })
}