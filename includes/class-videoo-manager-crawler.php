<?php
/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Videoo_Manager
 * @subpackage Videoo_Manager/includes
 */

class Videoo_Manager_Crawler extends WP_REST_Controller {

    public function __construct() {
        $this->namespace = 'videoo-manager/crawler';
        $this->resource_name = 'feed';
    }

    public function register_routes() {
        register_rest_route($this->namespace, $this->resource_name, [
            'methods' => 'GET',
            'callback' => [$this, 'get_items'],
            'args' => [
                'post_type' => [
                    'validate_callback' => function($param, $request, $key) {
                        return isset($param) && !empty($param) && is_string($param);
                    }
                ],
                'num' => [
                    'validate_callback' => function($param, $request, $key) {
                        return isset($param) && !empty($param) && is_numeric($param);
                    }
                ]
            ]
        ]);
    }

    public function get_items($request) {
        $token = $request->get_header('Videoo-Manager-Token');
        if (!isset($token) || empty($token) || $token !== 'a94c60ded52b0011ddf93a53fee89f06') {
            return new WP_Error('invalid-videoo-manager-security-token',
            'Invalid Videoo Manager Security Token.', ['status' => 403]);
        }

        $feed = [];
        $params = $request->get_query_params();
        $num = 10;
        $post_type = 'post';

        if (isset($params['num']) && !empty($params['num'])) {
            $num = $params['num'];
        }

        if (isset($params['post_type']) && !empty($params['post_type'])) {
            $post_type = $params['post_type'];
        }

        $query_args = [
            'post_type' => $post_type,
            'posts_per_page' => $num,
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        $posts = get_posts($query_args);

        foreach ($posts as $post) {
            $feed[] = [
                'post_title' => $post->post_title,
                'post_link' => get_the_permalink($post),
                'post_image_url' => get_the_post_thumbnail_url($post, 'full')
            ];
        }

        return new WP_REST_Response($feed);
    }

}
