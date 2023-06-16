<?php

namespace app\framework\EasyWechat\OfficialAccount;

//use EasyWeChat\Work\ExternalContact\Client as BaseClient;
use EasyWeChat\Kernel\Http\StreamResponse;
use EasyWeChat\Kernel\Messages\Article;
use EasyWeChat\OfficialAccount\Material\Client as BaseClient;


class Material extends BaseClient
{

    /**
     * @param $article_id
     * @param $index
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 删除已发布的文章
     */
    public function unsetArticle($article_id, $index = null)
    {
        $params = [
            'article_id' => $article_id,
        ];
        if (!is_null($index)) {
            $params['index'] = intval($index);
        }
        return $this->httpPostJson('cgi-bin/freepublish/delete', $params);
    }

    /**
     * @param $mediaId
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 发布文章
     */
    public function pushArticle($mediaId)
    {
        return $this->httpPostJson('cgi-bin/freepublish/submit', ['media_id' => $mediaId]);
    }


    /**
     * @param $offset
     * @param $count
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 获取已发布文章的列表
     */
    public function getArticleList($offset = 0, $count = 20, $no_content = 0)
    {
        return $this->httpPostJson('cgi-bin/freepublish/batchget', [
            'offset' => $offset,
            'count' => $count,
            'no_content' => $no_content,
        ]);
    }


    /**
     * @param $article_id
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 获取单篇已发布文章
     */
    public function getArticleOne($article_id)
    {
        return $this->httpPostJson('cgi-bin/freepublish/getarticle', ['article_id' => $article_id]);
    }


    /**
     * @param $publish_id
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 查询文章发布结果
     */
    public function checkArticleExamine($publish_id)
    {
        return $this->httpPostJson('cgi-bin/freepublish/get', ['publish_id' => $publish_id]);
    }


    /**
     * @param string $mediaId
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 微信草稿箱-删除草稿
     */
    public function draftDelete(string $mediaId)
    {
        return $this->httpPostJson('cgi-bin/draft/delete', ['media_id' => $mediaId]);
    }

    /**
     * @param int $offset
     * @param int $count
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 微信草稿箱-获取草稿列表
     */
    public function draftList(int $offset = 0, int $count = 20)
    {
        $params = [
            'no_content' => 0,
            'offset' => $offset,
            'count' => $count,
        ];

        return $this->httpPostJson('cgi-bin/draft/batchget', $params);
    }

    /**
     * @param string $mediaId
     * @return array|\EasyWeChat\Kernel\Http\Response|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 微信草稿箱-获取单个草稿
     */
    public function draftGet(string $mediaId)
    {
        $response = $this->requestRaw('cgi-bin/draft/get', 'POST', ['json' => ['media_id' => $mediaId]]);

        if (false !== stripos($response->getHeaderLine('Content-disposition'), 'attachment')) {
            return StreamResponse::buildFromPsrResponse($response);
        }

        return $this->castResponseToType($response, $this->app['config']->get('response_type'));
    }

    /**
     * @param string $mediaId
     * @param $article
     * @param int $index
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 微信草稿箱-编辑草稿
     */
    public function updateArticle(string $mediaId, $article, int $index = 0)
    {
        if ($article instanceof Article) {
            $article = $article->transformForJsonRequestWithoutType();
        }

        $params = [
            'media_id' => $mediaId,
            'index' => $index,
            'articles' => isset($article['title']) ? $article : (isset($article[$index]) ? $article[$index] : []),
        ];

        return $this->httpPostJson('cgi-bin/draft/update', $params);
    }


    public function getArticle(string $mediaId)
    {
        $params = [
            'media_id' => $mediaId,
        ];

        return $this->httpPostJson('cgi-bin/draft/get', $params);
    }


    /**
     * @param $articles
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * 微信草稿箱-创建草稿
     */
    public function uploadArticle($articles)
    {
        if ($articles instanceof Article || !empty($articles['title'])) {
            $articles = [$articles];
        }

        $params = ['articles' => array_map(function ($article) {
            if ($article instanceof Article) {
                return $article->transformForJsonRequestWithoutType();
            }

            return $article;
        }, $articles)];

        return $this->httpPostJson('cgi-bin/draft/add', $params);
    }

}
