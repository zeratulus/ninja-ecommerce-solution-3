<?php
class ControllerInformationSitemap extends Controller {
	public function index() {
		$this->load->language('information/sitemap');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/sitemap')
		);

		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories_1 = $this->model_catalog_category->getCategories(0);

		foreach ($categories_1 as $category_1) {
			$level_2_data = array();

			$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);

			foreach ($categories_2 as $category_2) {
				$level_3_data = array();

				$categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);

				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'name' => $category_3['name'],
						'href' => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'] . '_' . $category_3['category_id'])
					);
				}

				$level_2_data[] = array(
					'name'     => $category_2['name'],
					'children' => $level_3_data,
					'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'])
				);
			}

			$data['categories'][] = array(
				'name'     => $category_1['name'],
				'children' => $level_2_data,
				'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'])
			);
		}

		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['history'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['search'] = $this->url->link('product/search');
		$data['contact'] = $this->url->link('information/contact');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			$data['informations'][] = array(
				'title' => $result['title'],
				'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
			);
		}

		//Blog
		$this->load->model('blog/category');
		$this->load->model('blog/article');

		$data['text_blog'] = $this->config->get('configblog_name' . $this->language_id);
		$data['blog_categories'] = array();
		$categories = $this->model_blog_category->getCategories(0);
		foreach ($categories as $category) {
			// Level 2
			$children_data = array();

			$children = $this->model_blog_category->getCategories($category['blog_category_id']);
			foreach ($children as $child) {
				$filter_data = array(
					'filter_blog_category_id'  => $child['blog_category_id'],
					'filter_sub_category' => true
				);

				$articles_list = array();
				$articles = $this->model_blog_article->getArticlesList($this->config->get('config_language_id'), $child['blog_category_id']);
				foreach ($articles as $article) {
					$articles_list[] = array(
						'name' => $article['name'],
						'href' => $this->url->link('blog/article', 'article_id=' . $article['article_id'])
					);
				}

				$children_data[] = array(
					'name'  => $child['name'] . ($this->config->get('configblog_article_count') ? ' (' . $this->model_blog_article->getTotalArticles($filter_data) . ')' : ''),
					'href'  => $this->url->link('blog/category', 'blog_category_id=' . $category['blog_category_id'] . '_' . $child['blog_category_id']),
					'articles' => $articles_list
				);
			}

			// Level 1
			$articles_list = array();
			$articles = $this->model_blog_article->getArticlesList($this->config->get('config_language_id'), $category['blog_category_id']);
			foreach ($articles as $article) {
				$articles_list[] = array(
					'name' => $article['name'],
					'href' => $this->url->link('blog/article', 'article_id=' . $article['article_id'])
				);
			}

			$data['blog_categories'][] = array(
				'name'     => $category['name'],
				'children' => $children_data,
				'column'   => $category['column'] ? $category['column'] : 1,
				'href'     => $this->url->link('blog/category', 'blog_category_id=' . $category['blog_category_id']),
				'articles' => $articles_list
			);
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/sitemap', $data));
	}
}