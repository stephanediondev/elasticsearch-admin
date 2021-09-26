# Elasticsearch version tracking (for feature detection)

## 8.0.0-alpha2 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/master/release-notes-8.0.0-alpha2.html)

- Set xpack.security.enabled to true for all licenses #72300
- Autogenerate and print elastic password on startup #77291

## 8.0.0-alpha1 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/master/release-notes-8.0.0-alpha1.html)

- Remove deprecated endpoints containing _xpack #48170 (issue: #35958)

## 7.10.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/7.10/release-notes-7.10.0.html)

- Clone Snapshot API #61839

## 7.9.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/7.9/release-notes-7.9.0.html)

- Add support for data streams #58106 (issue: #53100)
- Implement dangling indices API #50920 (issue: #48366)

## 7.8.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/7.8/release-notes-7.8.0.html)

- Add support for V2 index templates to /_cat/templates #55829 (issue: #53101)
- Add prefer_v2_templates flag and index setting #55411 (issue: #53101)
- Use V2 index templates during index creation #54669 (issue: #53101)

## 7.7.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/7.7/release-notes-7.7.0.html)

- Allow _cat indices & aliases to use indices options #53248 (issue: #52304)
- Create GET _cat/transforms API Issue #53643 (issue: #51412)
- Add _cat/ml/data_frame/analytics API #52260 (issue: #51413)
- Add _cat/ml/trained_models API #51529 (issue: #51414)
- Add GET _cat/ml/datafeeds #51500 (issue: #51411)
- Add _cat/ml/anomaly_detectors API #51364
- Introduce formal role for remote cluster client #53924


## 7.5.0 [Release highlights](https://www.elastic.co/guide/en/elasticsearch/reference/7.5/release-highlights-7.5.0.html)

- Snapshot lifecycle management retention

## 7.4.0 [Release highlights](https://www.elastic.co/guide/en/elasticsearch/reference/7.4/release-highlights-7.4.0.html)

- Snapshot lifecycle management

## 7.3.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/7.3/release-notes-7.3.0.html)

- Add voting-only master node #43410 (issue: #14340)
- Support builtin privileges in get privileges API #42134 (issue: #29771)

## 7.1.0 [Release highlights](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/release-highlights-7.1.0.html)

- TLS is now licensed under the Elastic Basic license
- RBAC is now licensed under the Elastic Basic license

## 7.0.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/7.0/release-notes-7.0.0.html)

- Default to one shard #30539

## 6.6.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/6.6/release-notes-6.6.0.html)

- Adds Index lifecycle feature #35193
- Add a _freeze / _unfreeze API #35592 (issue: #34352)
- Add support for get license basic/trial status API #33176 (issue: #29827)
- Undeprecate /_license endpoints #35974 (issue: #35959)
- Option to use endpoints starting with _security #36379 (issue: #36293)

## 6.5.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/release-notes-6.5.0.html)

- Add cluster-wide shard limit warnings #34021 (issues: #20705, #32856)
- Add start trial API to HLRC #33406

## 6.4.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/6.4/release-notes-6.4.0.html)

- Reload secure settings for plugins - backport (#31383) #31481 (issue: #29135)
- Add cluster get settings API #31706 (issue: #27205)

## 6.2.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/6.2/release-notes-6.2.0.html)

- Allow _doc as a type. #27816 (issues: #27750, #27751)

## 6.1.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/6.1/release-notes-6.1.0.html)

- Implement adaptive replica selection #26128 (issue: #24915)

## 6.0.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/6.0/release-notes-6.0.0.html)

- Allows multiple patterns to be specified for index templates #21009 (issue: #20690)
- Adds nodes usage API to monitor usages of actions #24169

## 5.6.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/5.6/release-notes-5.6.0.html)

- Expand /_cat/nodes to return information about hard drive #21775 (issue: #21679)

## 5.4.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/5.4/release-notes-5.4.0.html)

- Add cross-cluster search remote cluster info API #23969 (issue: #23925)

## 5.1.1 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/5.1/release-notes-5.1.1.html)

- Adding built-in sorting capability to _cat apis. #20658 (issue: #16975)
- Provides a cat api endpoint for templates. #20545 (issue: #20467)

## 5.0.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/5.0/release-notes-5.0.0.html)

- Add API to explain why a shard is or isn’t assigned #17305 (issue: #14593)
- Remove Delete-By-Query plugin #18516 (issue: #18469)
- Add system CPU percent to OS stats #14741
- Modify load average format #15932 (issue: #15907)
- Reintroduce five-minute and fifteen-minute load averages on Linux #15907 (issues: #12049, #14741)
- Add REST _ingest/pipeline to get all pipelines #19603 (issue: #19585)
- Adds tombstones to cluster state for index deletions #17265 (issues: #16358, #17435)
- Extend reroute with an option to force assign stale primary shard copies #15708 (issue: #14739)

## 2.3.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/2.3/release-notes-2.3.0.html)

- Backport of task management api to 2x #16959

## 2.1.0 [Release notes](https://www.elastic.co/guide/en/elasticsearch/reference/2.1/release-notes-2.1.0.html)

- Add cat API for repositories and snapshots #14247 (issue: #13919)
- Add Force Merge API, deprecate Optimize API #13778
- Add os.allocated_processors stats #14409 (issue: #13917)
