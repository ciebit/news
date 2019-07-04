<?php
namespace Ciebit\News\Builders;

use Ciebit\News\Languages\Reference as LanguageReference;
use Ciebit\News\News;
use Ciebit\News\Status;
use DateTime;
use Exception;

use function is_numeric;
use function is_string;

class Builder
{
    public function build(array $data): News
    {
        $check = isset($data['title'])
        && is_string($data['title'])
        && isset($data['status'])
        && is_numeric($data['status']);

        if (! $check) {
            throw new Exception('ciebit.news.builders.dataInvalid', 1);
        }

        $news = new News($data['title'], new Status((int) $data['status']));

        isset($data['datetime'])
        && $news->setDateTime(new DateTime($data['datetime']));

        isset($data['id'])
        && $news->setId((string) $data['id']);

        isset($data['body'])
        && $news->setBody((string) $data['body']);

        isset($data['summary'])
        && $news->setSummary((string) $data['summary']);

        isset($data['uri'])
        && $news->setUri((string) $data['uri']);

        isset($data['views'])
        && is_numeric($data['views'])
        && $news->setViews((int) $data['views']);

        isset($data['language'])
        && $news->setLanguage($data['language']);

        isset($data['status'])
        && $news->setStatus(new Status((int) $data['status']));

        if (isset($data['languages_references'])) {
            $languageReferences = json_decode($data['languages_references'], true);
            foreach ($languageReferences as $languageCode => $id) {
                $news->addLanguageReference(new LanguageReference($languageCode, (string) $id));
            }
        }

        return $news;
    }
}
