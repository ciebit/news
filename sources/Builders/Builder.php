<?php
namespace Ciebit\News\Builders;

use Ciebit\News\Languages\Reference as LanguageReference;
use Ciebit\News\News;
use Ciebit\News\Status;
use DateTime;
use Exception;

use function is_numeric;

class Builder
{
    public static function build(array $data): News
    {
        $check = isset($data['title'])
        && isset($data['status']);

        if (! $check) {
            throw new Exception('ciebit.news.builders.dataInvalid', 1);
        }

        $status = $data['status'];
        if (! is_object($status) || ($status instanceof Status)) {
            $status = new Status((int) $data['status']);
        }

        $news = new News($data['title'], $status);

        isset($data['authorId'])
        && $news->setAuthorId((string) $data['authorId']);

        isset($data['body'])
        && $news->setBody((string) $data['body']);

        isset($data['coverId'])
        && $news->setCoverId((string) $data['coverId']);

        if (isset($data['dateTime'])) {
            $dateTime = $data['dateTime'];
            if (! is_object($dateTime) || ! ($dateTime instanceof DateTime)) {
                $dateTime = new DateTime($dateTime);
            }
            $news->setDateTime($dateTime);
        }

        isset($data['id'])
        && $news->setId((string) $data['id']);

        isset($data['language'])
        && $news->setLanguage($data['language']);

        isset($data['slug'])
        && $news->setSlug((string) $data['slug']);

        isset($data['summary'])
        && $news->setSummary((string) $data['summary']);

        isset($data['uri'])
        && $news->setUri((string) $data['uri']);

        isset($data['views'])
        && is_numeric($data['views'])
        && $news->setViews((int) $data['views']);

        isset($data['labelsId'])
        && !empty($data['labelsId'])
        && is_array($data['labelsId'])
        && $news->setLabelsId(...$data['labelsId']);

        if (
            isset($data['languagesReferences'])
            && !empty($data['languagesReferences'])
        ) {
            $languageReferences = $data['languagesReferences'];

            if (is_string($languageReferences)) {
                $languageReferences = json_decode($languageReferences, true);
            }

            foreach ($languageReferences as $languageCode => $id) {
                $news->addLanguageReference(new LanguageReference($languageCode, (string) $id));
            }
        }

        return $news;
    }
}
