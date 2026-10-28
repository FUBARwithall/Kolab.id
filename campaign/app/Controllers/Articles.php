<?php

namespace App\Controllers;

use App\Models\ArticleModel;

class Articles extends BaseController
{
    protected ArticleModel $articleModel;

    public function __construct()
    {
        $this->articleModel = new ArticleModel();
    }

    /**
     * Halaman Utama CMS Artikel Landing Publik
     */
    public function index()
    {
        $data = [
            'title'      => 'Artikel Landing CMS — Publikasi Konten Blog',
            'controller' => 'Articles',
        ];

        return view('articles/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Direct access not allowed']);
        }

        $draw   = (int) ($this->request->getPost('draw') ?? 1);
        $start  = (int) ($this->request->getPost('start') ?? 0);
        $length = (int) ($this->request->getPost('length') ?? 10);
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $orderColIdx = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'articles.id',
            1 => 'articles.title',
            2 => 'articles.category',
            3 => 'articles.status',
            4 => 'articles.published_at',
            5 => 'articles.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'articles.id';

        $result = $this->articleModel->getDatatables($start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $isPublished = strtolower($row['status']) === 'published';
            $statusBadge = $isPublished
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>PUBLISHED</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="fas fa-file me-1"></i>DRAFT</span>';

            $formattedData[] = [
                'id'       => $row['id'],
                'title'    => '
                    <div class="fw-bold text-dark">' . esc($row['title']) . '</div>
                    <small class="text-muted font-monospace">slug: ' . esc($row['slug']) . '</small>
                ',
                'category' => '<span class="badge bg-light text-dark border">' . esc($row['category'] ?: 'Umum') . '</span>',
                'status'   => $statusBadge,
                'published'=> !empty($row['published_at']) ? date('d M Y H:i', strtotime($row['published_at'])) : '<span class="text-muted fst-italic">Draft</span>',
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Artikel" onclick="editArticle(' . $row['id'] . ')"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Artikel" onclick="deleteArticle(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
                    </div>
                ',
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data'            => $formattedData,
        ]);
    }

    /**
     * Get single article JSON
     */
    public function getOne(int $id)
    {
        $article = $this->articleModel->find($id);
        if (!$article) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Artikel tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $article]);
    }

    /**
     * Save article
     */
    public function save()
    {
        $session  = session();
        $authorId = (int) ($session->get('user_id') ?? 1);

        $id       = $this->request->getPost('id');
        $title    = trim((string)$this->request->getPost('title'));
        $category = trim((string)$this->request->getPost('category'));
        $excerpt  = trim((string)$this->request->getPost('excerpt'));
        $content  = (string)$this->request->getPost('content');
        $status   = strtoupper(trim((string)$this->request->getPost('status') ?: 'PUBLISHED'));

        if (empty($title) || empty($content)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Judul dan konten artikel wajib diisi',
            ]);
        }

        // Generate slug
        $baseSlug = url_title($title, '-', true);
        $slug = $baseSlug;
        $counter = 1;
        while ($this->articleModel->where('slug', $slug)->where('id !=', (int)$id)->first()) {
            $slug = $baseSlug . '-' . (++$counter);
        }

        $data = [
            'title'        => $title,
            'slug'         => $slug,
            'category'     => $category ?: 'Insight',
            'excerpt'      => $excerpt ?: substr(strip_tags($content), 0, 160) . '...',
            'content'      => $content,
            'status'       => $status,
            'author_id'    => $authorId,
            'published_at' => ($status === 'PUBLISHED') ? date('Y-m-d H:i:s') : null,
        ];

        if (!empty($id)) {
            $this->articleModel->update($id, $data);
            $articleId = (int)$id;
        } else {
            $articleId = $this->articleModel->insert($data, true);
        }

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Artikel berhasil disimpan dan disinkronisasi ke feed landing page!',
            'article_id' => $articleId,
        ]);
    }

    /**
     * Delete article
     */
    public function delete(int $id)
    {
        $this->articleModel->delete($id);
        return $this->response->setJSON(['success' => true, 'message' => 'Artikel berhasil dihapus']);
    }
}
