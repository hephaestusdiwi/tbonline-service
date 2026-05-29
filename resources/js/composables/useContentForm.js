// composables/useContentForm.js
import { ref, computed, watch } from 'vue'
import { useContentStore } from '@/store/useContentStore'
import { useRouter } from 'vue-router'

export function useContentForm(type = 'article', existingContent = null) {
  const store  = useContentStore()
  const router = useRouter()

  // ── Form state ─────────────────────────────────────────────
  const form = ref({
    type,
    title:            existingContent?.title            ?? '',
    slug:             existingContent?.slug             ?? '',
    body:             existingContent?.body             ?? '',
    excerpt:          existingContent?.excerpt          ?? '',
    tags:             existingContent?.tags             ?? [],
    status:           existingContent?.status           ?? 'draft',
    meta_title:       existingContent?.meta_title       ?? '',
    meta_description: existingContent?.meta_description ?? '',
    thumbnail:        null,   // File object
  })

  const thumbnailPreview  = ref(existingContent?.thumbnail ?? null)
  const tagInput          = ref('')
  const errors            = ref({})
  const successMessage    = ref('')

  // ── Helpers ────────────────────────────────────────────────
  const isArticle  = computed(() => type === 'article')
  const isEditing  = computed(() => !!existingContent)

  const user = JSON.parse(localStorage.getItem('user') || '{}')
  const userRole = user.role || ''
  
  const isStaff    = computed(() => userRole === 'staff')
  const canPublish = computed(() => userRole === 'admin' || userRole === 'manager')

  // Auto-slug dari title (hanya saat create, sebelum user edit manual)
  const slugEdited = ref(false)
  watch(() => form.value.title, (val) => {
    if (!isEditing.value && !slugEdited.value) {
      form.value.slug = val
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim()
    }
  })

  // ── Thumbnail ──────────────────────────────────────────────
  function onThumbnailChange(event) {
    const file = event.target.files[0]
    if (!file) return
    form.value.thumbnail = file
    thumbnailPreview.value = URL.createObjectURL(file)
  }

  function removeThumbnail() {
    form.value.thumbnail = null
    thumbnailPreview.value = null
  }

  // ── Tags ───────────────────────────────────────────────────
  function addTag() {
    const t = tagInput.value.trim().toLowerCase()
    if (t && !form.value.tags.includes(t)) {
      form.value.tags.push(t)
    }
    tagInput.value = ''
  }

  function removeTag(index) {
    form.value.tags.splice(index, 1)
  }

  // ── Validation ─────────────────────────────────────────────
  function validate() {
    errors.value = {}

    if (!form.value.title.trim())
      errors.value.title = 'Judul wajib diisi.'

    if (!form.value.body.trim())
      errors.value.body = 'Konten wajib diisi.'

    if (form.value.slug && !/^[a-z0-9-]+$/.test(form.value.slug))
      errors.value.slug = 'Slug hanya boleh huruf kecil, angka, dan tanda hubung.'

    if (form.value.meta_description && form.value.meta_description.length > 300)
      errors.value.meta_description = 'Meta description maksimal 300 karakter.'

    return Object.keys(errors.value).length === 0
  }

  // ── Submit ─────────────────────────────────────────────────
  async function submit(redirectTo = null) {
    if (!validate()) return

    successMessage.value = ''
    errors.value         = {}

    // Staff hanya bisa simpan draft
    const payload = { ...form.value }
    if (isStaff.value) payload.status = 'draft'

    try {
      if (isEditing.value) {
        await store.update(existingContent.id, payload)
      } else {
        const created = await store.create(payload)
        if (redirectTo) {
          router.push(redirectTo.replace(':id', created.id))
          return
        }
      }
      successMessage.value = isEditing.value
        ? 'Perubahan berhasil disimpan.'
        : 'Konten berhasil dibuat.'
    } catch (e) {
      // Validasi dari Laravel (422)
      if (e.response?.status === 422) {
        const laravelErrors = e.response.data.errors ?? {}
        Object.assign(errors.value, laravelErrors)
      }
    }
  }

  return {
    form,
    errors,
    successMessage,
    thumbnailPreview,
    tagInput,
    isArticle,
    isEditing,
    isStaff,
    canPublish,
    onThumbnailChange,
    removeThumbnail,
    addTag,
    removeTag,
    slugEdited,
    submit,
  }
}